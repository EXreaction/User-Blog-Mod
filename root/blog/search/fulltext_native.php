<?php
/**
*
* @package phpBB3 User Blog Search
* @version $Id: fulltext_native.php 485 2008-08-15 23:33:57Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction, Lithium Studios; phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
* Custom Search class for Blogs
*
* Most of this code is forked from the search code in phpBB3.
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

include_once($phpbb_root_path . 'blog/search/search.' . $phpEx);

/**
* fulltext_native
* phpBB's own db driven fulltext search, version 2
* @package search
*/
class blog_fulltext_native extends blog_search
{
	var $stats = array();
	var $word_length = array();
	var $search_query;
	var $common_words = array();

	var $must_contain_ids = array();
	var $must_not_contain_ids = array();
	var $must_exclude_one_ids = array();
	
	/**
	* Initialises the fulltext_native search backend with min/max word length and makes sure the UTF-8 normalizer is loaded.
	*
	* @access	public
	*/
	function blog_fulltext_native()
	{
		global $phpbb_root_path, $phpEx, $config;

		$this->word_length = array('min' => $config['fulltext_native_min_chars'], 'max' => $config['fulltext_native_max_chars']);

		/**
		* Load the UTF tools
		*/
		if (!class_exists('utf_normalizer'))
		{
			include($phpbb_root_path . 'includes/utf/utf_normalizer.' . $phpEx);
		}
	}

	/**
	* Re-Index all blogs/replies
	*/
	function reindex()
	{
		global $db;

		$this->delete_index();

		$sql = 'SELECT * FROM ' . BLOGS_TABLE . '
			WHERE blog_deleted = 0
				AND blog_approved = 1';
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$this->index('add', $row['blog_id'], 0, $row['blog_text'], $row['blog_subject'], $row['user_id']);
		}

		$sql = 'SELECT * FROM ' . BLOGS_REPLY_TABLE . '
			WHERE reply_deleted = 0
				AND reply_approved = 1';
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$this->index('add', $row['blog_id'], $row['reply_id'], $row['reply_text'], $row['reply_subject'], $row['user_id']);
		}
	}

	/**
	* This function fills $this->search_query with the cleaned user search query.
	*
	* If $terms is 'any' then the words will be extracted from the search query
	* and combined with | inside brackets. They will afterwards be treated like
	* an standard search query.
	*
	* Then it analyses the query and fills the internal arrays $must_not_contain_ids,
	* $must_contain_ids and $must_exclude_one_ids which are later used by keyword_search().
	*
	* @param	string	$keywords	contains the search query string as entered by the user
	* @param	string	$terms		is either 'all' (use search query as entered, default words to 'must be contained in post')
	* 	or 'any' (find all posts containing at least one of the given words)
	* @return	boolean				false if no valid keywords were found and otherwise true
	*
	* @access	public
	*/
	function split_keywords($keywords, $terms)
	{
		global $db, $user;

		$keywords = trim($this->cleanup($keywords, '+-|()*'));

		// allow word|word|word without brackets
		if ((strpos($keywords, ' ') === false) && (strpos($keywords, '|') !== false) && (strpos($keywords, '(') === false))
		{
			$keywords = '(' . $keywords . ')';
		}

		$open_bracket = $space = false;
		for ($i = 0, $n = strlen($keywords); $i < $n; $i++)
		{
			if ($open_bracket !== false)
			{
				switch ($keywords[$i])
				{
					case ')':
						if ($open_bracket + 1 == $i)
						{
							$keywords[$i - 1] = '|';
							$keywords[$i] = '|';
						}
						$open_bracket = false;
					break;
					case '(':
						$keywords[$i] = '|';
					break;
					case '+':
					case '-':
					case ' ':
						$keywords[$i] = '|';
					break;
				}
			}
			else
			{
				switch ($keywords[$i])
				{
					case ')':
						$keywords[$i] = ' ';
					break;
					case '(':
						$open_bracket = $i;
						$space = false;
					break;
					case '|':
						$keywords[$i] = ' ';
					break;
					case '-':
					case '+':
						$space = $keywords[$i];
					break;
					case ' ':
						if ($space !== false)
						{
							$keywords[$i] = $space;
						}
					break;
					default:
						$space = false;
				}
			}
		}

		if ($open_bracket)
		{
			$keywords .= ')';
		}

		$match = array(
			'#  +#',
			'#\|\|+#',
			'#(\+|\-)(?:\+|\-)+#',
			'#\(\|#',
			'#\|\)#',
		);
		$replace = array(
			' ',
			'|',
			'$1',
			'(',
			')',
		);

		$keywords = preg_replace($match, $replace, $keywords);

		// $keywords input format: each word separated by a space, words in a bracket are not separated

		// the user wants to search for any word, convert the search query
		if ($terms == 'any')
		{
			$words = array();

			preg_match_all('#([^\\s+\\-|()]+)(?:$|[\\s+\\-|()])#u', $keywords, $words);
			if (sizeof($words[1]))
			{
				$keywords = '(' . implode('|', $words[1]) . ')';
			}
		}

		// set the search_query which is shown to the user
		$this->search_query = $keywords;

		$exact_words = array();
		preg_match_all('#([^\\s+\\-|*()]+)(?:$|[\\s+\\-|()])#u', $keywords, $exact_words);
		$exact_words = $exact_words[1];

		$common_ids = $words = array();

		if (sizeof($exact_words))
		{
			$sql = 'SELECT word_id, word_text, word_common
				FROM ' . BLOG_SEARCH_WORDLIST_TABLE . '
				WHERE ' . $db->sql_in_set('word_text', $exact_words);
			$result = $db->sql_query($sql);

			// store an array of words and ids, remove common words
			while ($row = $db->sql_fetchrow($result))
			{
				if ($row['word_common'])
				{
					$this->common_words[] = $row['word_text'];
					$common_ids[$row['word_text']] = (int) $row['word_id'];
					continue;
				}

				$words[$row['word_text']] = (int) $row['word_id'];
			}
			$db->sql_freeresult($result);
		}
		unset($exact_words);

		// now analyse the search query, first split it using the spaces
		$query = explode(' ', $keywords);

		$this->must_contain_ids = array();
		$this->must_not_contain_ids = array();
		$this->must_exclude_one_ids = array();

		$mode = '';
		$ignore_no_id = true;

		foreach ($query as $word)
		{
			if (empty($word))
			{
				continue;
			}

			// words which should not be included
			if ($word[0] == '-')
			{
				$word = substr($word, 1);

				// a group of which at least one may not be in the resulting posts
				if ($word[0] == '(')
				{
					$word = array_unique(explode('|', substr($word, 1, -1)));
					$mode = 'must_exclude_one';
				}
				// one word which should not be in the resulting posts
				else
				{
					$mode = 'must_not_contain';
				}
				$ignore_no_id = true;
			}
			// words which have to be included
			else
			{
				// no prefix is the same as a +prefix
				if ($word[0] == '+')
				{
					$word = substr($word, 1);
				}

				// a group of words of which at least one word should be in every resulting post
				if ($word[0] == '(')
				{
					$word = array_unique(explode('|', substr($word, 1, -1)));
				}
				$ignore_no_id = false;
				$mode = 'must_contain';
			}

			if (empty($word))
			{
				continue;
			}

			// if this is an array of words then retrieve an id for each
			if (is_array($word))
			{
				$non_common_words = array();
				$id_words = array();
				foreach ($word as $i => $word_part)
				{
					if (strpos($word_part, '*') !== false)
					{
						$id_words[] = '\'' . $db->sql_escape(str_replace('*', '%', $word_part)) . '\'';
						$non_common_words[] = $word_part;
					}
					else if (isset($words[$word_part]))
					{
						$id_words[] = $words[$word_part];
						$non_common_words[] = $word_part;
					}
					else
					{
						$len = utf8_strlen($word_part);
						if ($len < $this->word_length['min'] || $len > $this->word_length['max'])
						{
							$this->common_words[] = $word_part;
						}
					}
				}
				if (sizeof($id_words))
				{
					sort($id_words);
					if (sizeof($id_words) > 1)
					{
						$this->{$mode . '_ids'}[] = $id_words;
					}
					else
					{
						$mode = ($mode == 'must_exclude_one') ? 'must_not_contain' : $mode;
						$this->{$mode . '_ids'}[] = $id_words[0];
					}
				}
				// throw an error if we shall not ignore unexistant words
				else if (!$ignore_no_id && sizeof($non_common_words))
				{
					trigger_error(sprintf($user->lang['WORDS_IN_NO_POST'], implode(', ', $non_common_words)));
				}
				unset($non_common_words);
			}
			// else we only need one id
			else if (($wildcard = strpos($word, '*') !== false) || isset($words[$word]))
			{
				if ($wildcard)
				{
					$len = utf8_strlen(str_replace('*', '', $word));
					if ($len >= $this->word_length['min'] && $len <= $this->word_length['max'])
					{
						$this->{$mode . '_ids'}[] = '\'' . $db->sql_escape(str_replace('*', '%', $word)) . '\'';
					}
					else
					{
						$this->common_words[] = $word;
					}
				}
				else
				{
					$this->{$mode . '_ids'}[] = $words[$word];
				}
			}
			// throw an error if we shall not ignore unexistant words
			else if (!$ignore_no_id)
			{
				if (!isset($common_ids[$word]))
				{
					$len = utf8_strlen($word);
					if ($len >= $this->word_length['min'] && $len <= $this->word_length['max'])
					{
						trigger_error(sprintf($user->lang['WORD_IN_NO_POST'], $word));
					}
					else
					{
						$this->common_words[] = $word;
					}
				}
			}
			else
			{
				$len = utf8_strlen($word);
				if ($len < $this->word_length['min'] || $len > $this->word_length['max'])
				{
					$this->common_words[] = $word;
				}
			}
		}

		// we can't search for negatives only
		if (!sizeof($this->must_contain_ids))
		{
			return false;
		}

		sort($this->must_contain_ids);
		sort($this->must_not_contain_ids);
		sort($this->must_exclude_one_ids);

		if (!empty($this->search_query))
		{
			return true;
		}
		return false;
	}

	/**
	* Performs a search on keywords depending on display specific params. You have to run split_keywords() first.
	*
	* @param	string		$fields			contains either titleonly (topic titles should be searched), msgonly (only message bodies should be searched), blog (only subject and body of blogs should be searched) or all (all post bodies and subjects should be searched)
	* @param	string		$terms			is either 'all' (use query as entered, words without prefix should default to "have to be in field") or 'any' (ignore search query parts and just return all posts that contain any of the specified words)
	* @param	int			$blog_id			is set to 0 or a blog id, if it is not 0 then only posts in this blog should be searched
	*
	* @access	public
	*/
	function keyword_search($fields = 'all', $terms = 'all', $blog_id = 0)
	{
		global $config, $db, $user;

		// No keywords? No posts.
		if (empty($this->search_query))
		{
			return false;
		}

		if (!sizeof($this->must_contain_ids) && !sizeof($this->must_not_contain_ids) && !sizeof($this->must_exclude_one_ids))
		{
			$ignored = (sizeof($this->common_words)) ? sprintf($user->lang['IGNORED_TERMS_EXPLAIN'], implode(' ', $this->common_words)) . '<br />' : '';
			trigger_error($ignored . sprintf($user->lang['NO_KEYWORDS'], $this->word_length['min'], $this->word_length['max']));
		}

		$m_num = 0;
		$w_num = 0;
		$title_match = '';

		if ($fields == 'titleonly')
		{
			$title_match = 'title_match = 1';
		}
		else if ($fields == 'msgonly')
		{
			$title_match = 'title_match = 0';
		}

		$sql_array = array(
			'SELECT'	=> 'm0.blog_id, m0.reply_id',
			'FROM'		=> array(
				BLOG_SEARCH_WORDMATCH_TABLE	=> array(),
				BLOG_SEARCH_WORDLIST_TABLE	=> array(),
			),
			'LEFT_JOIN'	=> array(),
		);
		$sql_where = array();

		foreach ($this->must_contain_ids as $subquery)
		{
			if (is_array($subquery))
			{
				$group_by = true;

				$word_id_sql = array();
				$word_ids = array();
				foreach ($subquery as $id)
				{
					if (is_string($id))
					{
						$sql_array['LEFT_JOIN'][] = array(
							'FROM'	=> array(BLOG_SEARCH_WORDLIST_TABLE => 'w' . $w_num),
							'ON'	=> "w$w_num.word_text LIKE $id"
						);
						$word_ids[] = "w$w_num.word_id";

						$w_num++;
					}
					else
					{
						$word_ids[] = $id;
					}
				}

				$sql_where[] = $db->sql_in_set("m$m_num.word_id", $word_ids);

				unset($word_id_sql);
				unset($word_ids);
			}
			else if (is_string($subquery))
			{
				$sql_array['FROM'][BLOG_SEARCH_WORDLIST_TABLE][] = 'w' . $w_num;

				$sql_where[] = "w$w_num.word_text LIKE $subquery";
				$sql_where[] = "m$m_num.word_id = w$w_num.word_id";

				$group_by = true;
				$w_num++;
			}
			else
			{
				$sql_where[] = "m$m_num.word_id = $subquery";
			}

			$sql_array['FROM'][BLOG_SEARCH_WORDMATCH_TABLE][] = 'm' . $m_num;

			if ($title_match)
			{
				$sql_where[] = "m$m_num.$title_match";
			}

			if ($m_num != 0)
			{
				$sql_where[] = "m$m_num.blog_id = m0.blog_id";
				$sql_where[] = "m$m_num.reply_id = m0.reply_id";
			}
			$m_num++;
		}

		foreach ($this->must_not_contain_ids as $key => $subquery)
		{
			if (is_string($subquery))
			{
				$sql_array['LEFT_JOIN'][] = array(
					'FROM'	=> array(BLOG_SEARCH_WORDLIST_TABLE => 'w' . $w_num),
					'ON'	=> "w$w_num.word_text LIKE $subquery"
				);

				$this->must_not_contain_ids[$key] = "w$w_num.word_id";

				$group_by = true;
				$w_num++;
			}
		}

		if (sizeof($this->must_not_contain_ids))
		{
			$sql_array['LEFT_JOIN'][] = array(
				'FROM'	=> array(SEARCH_WORDMATCH_TABLE => 'm' . $m_num),
				'ON'	=> $db->sql_in_set("m$m_num.word_id", $this->must_not_contain_ids) . (($title_match) ? " AND m$m_num.$title_match" : '')// . " AND m$m_num.blog_id = m0.blog_id AND m$m_num.reply_id = m0.reply_id"
			);

			$sql_where[] = "m$m_num.word_id IS NULL";
			$m_num++;
		}

		foreach ($this->must_exclude_one_ids as $ids)
		{
			$is_null_joins = array();
			foreach ($ids as $id)
			{
				if (is_string($id))
				{
					$sql_array['LEFT_JOIN'][] = array(
						'FROM'	=> array(BLOG_SEARCH_WORDLIST_TABLE => 'w' . $w_num),
						'ON'	=> "w$w_num.word_text LIKE $id"
					);
					$id = "w$w_num.word_id";

					$group_by = true;
					$w_num++;
				}

				$sql_array['LEFT_JOIN'][] = array(
					'FROM'	=> array(BLOG_SEARCH_WORDMATCH_TABLE => 'm' . $m_num),
					'ON'	=> "m$m_num.word_id = $id AND m$m_num.blog_id = m0.blog_id AND m$m_num.reply_id = m0.reply_id" . (($title_match) ? " AND m$m_num.$title_match" : '')
				);
				$is_null_joins[] = "m$m_num.word_id IS NULL";

				$m_num++;
			}
			$sql_where[] = '(' . implode(' OR ', $is_null_joins) . ')';
		}

		if ($fields == 'blog')
		{
			$sql_where[] = 'm0.reply_id = 0';
		}

		if ($blog_id)
		{
			$sql_where[] = 'm0.blog_id =  ' . intval($blog_id);
		}

		$sql_array['WHERE'] = implode(' AND ', $sql_where);

		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);
		$ids = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$ids[] = $row;
		}
		$db->sql_freeresult($result);

		return $ids;
	}

	/**
	* Performs a search on an author's posts without caring about message contents. Depends on display specific params
	*
	* @param	array			$author_ary		an array of author ids
	* @param	int			$blog_id			is set to 0 or a blog id, if it is not 0 then only posts in this blog should be searched
	* @param	boolean		$firstpost_only		if true, only blog starting posts will be considered
	*
	* @access	public
	*/
	function author_search($author_ary, $blog_id = 0, $firstpost_only = false, $start = 0, $limit = 20)
	{
		global $config, $db;

		if (!is_array($author_ary))
		{
			$author_ary = array($author_ary);
		}

		$ids = array();

		$sql = 'SELECT blog_id FROM ' . BLOGS_TABLE . '
			WHERE ' . $db->sql_in_set('user_id', $author_ary) .
			(($blog_id) ? " AND blog_id = {$blog_id}" : '');
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
			$ids[$row['blog_id']] = array(0);
		}
		$db->sql_freeresult($result);

		if (!$firstpost_only && !$blog_id)
		{
			$sql = 'SELECT blog_id, reply_id FROM ' . BLOGS_REPLY_TABLE . '
				WHERE ' . $db->sql_in_set('user_id', $author_ary);
			$result = $db->sql_query($sql);
			while($row = $db->sql_fetchrow($result))
			{
				if (!isset($ids[$row['blog_id']]))
				{
					$ids[$row['blog_id']] = array($row['reply_id']);
				}
				else
				{
					$ids[$row['blog_id']][] = $row['reply_id'];
				}
			}
			$db->sql_freeresult($result);
		}

		$new_ids = array();
		if (sizeof($ids))
		{
			foreach ($ids as $id => $ary)
			{
				foreach ($ary as $rid)
				{
					$new_ids[] = array('blog_id' => $id, 'reply_id' => $rid);
				}
			}
		}

		return $new_ids;
	}

	/**
	* Split a text into words of a given length
	*
	* The text is converted to UTF-8, cleaned up, and split. Then, words that
	* conform to the defined length range are returned in an array.
	*
	* NOTE: duplicates are NOT removed from the return array
	*
	* @param	string	$text	Text to split, encoded in UTF-8
	* @return	array			Array of UTF-8 words
	*
	* @access	private
	*/
	function split_message($text)
	{
		global $phpbb_root_path, $phpEx, $user;

		$match = $words = array();

		/**
		* Taken from the original code
		*/
		// Do not index code
		$match[] = '#\[code(?:=.*?)?(\:?[0-9a-z]{5,})\].*?\[\/code(\:?[0-9a-z]{5,})\]#is';
		// BBcode
		$match[] = '#\[\/?[a-z0-9\*\+\-]+(?:=.*?)?(?::[a-z])?(\:?[0-9a-z]{5,})\]#';

		$min = $this->word_length['min'];
		$max = $this->word_length['max'];

		$isset_min = $min - 1;

		/**
		* Clean up the string, remove HTML tags, remove BBCodes
		*/
		$word = strtok($this->cleanup(preg_replace($match, ' ', strip_tags($text)), -1), ' ');

		while (strlen($word))
		{
			if (strlen($word) > 255 || strlen($word) <= $isset_min)
			{
				/**
				* Words longer than 255 bytes are ignored. This will have to be
				* changed whenever we change the length of search_wordlist.word_text
				*
				* Words shorter than $isset_min bytes are ignored, too
				*/
				$word = strtok(' ');
				continue;
			}

			$len = utf8_strlen($word);

			/**
			* Test whether the word is too short to be indexed.
			*
			* Note that this limit does NOT apply to CJK and Hangul
			*/
			if ($len < $min)
			{
				/**
				* Note: this could be optimized. If the codepoint is lower than Hangul's range
				* we know that it will also be lower than CJK ranges
				*/
				if ((strncmp($word, UTF8_HANGUL_FIRST, 3) < 0 || strncmp($word, UTF8_HANGUL_LAST, 3) > 0)
				 && (strncmp($word, UTF8_CJK_FIRST, 3) < 0 || strncmp($word, UTF8_CJK_LAST, 3) > 0)
				 && (strncmp($word, UTF8_CJK_B_FIRST, 4) < 0 || strncmp($word, UTF8_CJK_B_LAST, 4) > 0))
				{
					$word = strtok(' ');
					continue;
				}
			}

			$words[] = $word;
			$word = strtok(' ');
		}

		return $words;
	}

	/**
	* Updates wordlist and wordmatch tables when a message is posted or changed
	*
	* @param	string	$mode		Contains the post mode: edit, add
	* @param	int		$blog_id	The id of the blog which is modified/created
	* @param	int		$reply_id	The id of the reply which is modified/created - 0 if not a reply
	* @param	string	&$message	New or updated post content
	* @param	string	&$subject	New or updated post subject
	* @param	int		$poster_id	Post author's user id
	*
	* @access	public
	*/
	function index($mode, $blog_id, $reply_id, &$message, &$subject, $poster_id)
	{
		global $config, $db, $user;

		if (!$config['user_blog_search'])
		{
			return;
		}

		$blog_id = (int) $blog_id;
		$reply_id = (int) $reply_id;

		/* This should not be needed, so it is being commented out for now
		$sql = 'SELECT word_id FROM ' . BLOG_SEARCH_WORDMATCH_TABLE . "
			WHERE blog_id = '{$blog_id}'
			AND reply_id = '{$reply_id}'
				LIMIT 1";
		$result = $db->sql_query($sql);
		if ($db->sql_fetchrow($result))
		{
			$mode == 'edit';
		}
		$db->sql_freeresult($result);
		*/

		// Split old and new post/subject to obtain array of 'words'
		$split_text = $this->split_message($message);
		$split_title = $this->split_message($subject);

		$cur_words = array('post' => array(), 'title' => array());

		$words = array();
		if ($mode == 'edit')
		{
			$words['add']['post'] = array();
			$words['add']['title'] = array();
			$words['del']['post'] = array();
			$words['del']['title'] = array();

			$sql = 'SELECT w.word_id, w.word_text, m.title_match
				FROM ' . BLOG_SEARCH_WORDLIST_TABLE . ' w, ' . BLOG_SEARCH_WORDMATCH_TABLE . " m
				WHERE m.blog_id = $blog_id
					AND m.reply_id = $reply_id
					AND w.word_id = m.word_id";
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$which = ($row['title_match']) ? 'title' : 'post';
				$cur_words[$which][$row['word_text']] = $row['word_id'];
			}
			$db->sql_freeresult($result);

			$words['add']['post'] = array_diff($split_text, array_keys($cur_words['post']));
			$words['add']['title'] = array_diff($split_title, array_keys($cur_words['title']));
			$words['del']['post'] = array_diff(array_keys($cur_words['post']), $split_text);
			$words['del']['title'] = array_diff(array_keys($cur_words['title']), $split_title);
		}
		else
		{
			$words['add']['post'] = $split_text;
			$words['add']['title'] = $split_title;
			$words['del']['post'] = array();
			$words['del']['title'] = array();
		}
		unset($split_text);
		unset($split_title);

		// Get unique words from the above arrays
		$unique_add_words = array_unique(array_merge($words['add']['post'], $words['add']['title']));
		
		// We now have unique arrays of all words to be added and removed and
		// individual arrays of added and removed words for text and title. What
		// we need to do now is add the new words (if they don't already exist)
		// and then add (or remove) matches between the words and this post
		if (sizeof($unique_add_words))
		{
			$sql = 'SELECT word_id, word_text
				FROM ' . BLOG_SEARCH_WORDLIST_TABLE . '
				WHERE ' . $db->sql_in_set('word_text', $unique_add_words);
			$result = $db->sql_query($sql);

			$word_ids = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$word_ids[$row['word_text']] = $row['word_id'];
			}
			$db->sql_freeresult($result);
			$new_words = array_diff($unique_add_words, array_keys($word_ids));

			$db->sql_transaction('begin');
			if (sizeof($new_words))
			{
				$sql_ary = array();

				foreach ($new_words as $word)
				{
					$sql_ary[] = array('word_text' => (string) $word, 'word_count' => 0);
				}
				$db->sql_return_on_error(true);
				$db->sql_multi_insert(BLOG_SEARCH_WORDLIST_TABLE, $sql_ary);
				$db->sql_return_on_error(false);
			}
			unset($new_words, $sql_ary);
		}
		else
		{
			$db->sql_transaction('begin');
		}

		// now update the search match table, remove links to removed words and add links to new words
		foreach ($words['del'] as $word_in => $word_ary)
		{
			$title_match = ($word_in == 'title') ? 1 : 0;

			if (sizeof($word_ary))
			{
				$sql_in = array();
				foreach ($word_ary as $word)
				{
					$sql_in[] = $cur_words[$word_in][$word];
				}

				$sql = 'DELETE FROM ' .  BLOG_SEARCH_WORDMATCH_TABLE . '
					WHERE ' . $db->sql_in_set('word_id', $sql_in) . "
						AND blog_id = $blog_id
						AND reply_id = $reply_id
						AND title_match = $title_match";
				$db->sql_query($sql);

				$sql = 'UPDATE ' . BLOG_SEARCH_WORDLIST_TABLE . '
					SET word_count = word_count - 1
					WHERE ' . $db->sql_in_set('word_id', $sql_in) . '
						AND word_count > 0';
				$db->sql_query($sql);

				unset($sql_in);
			}
		}

		$db->sql_return_on_error(true);
		foreach ($words['add'] as $word_in => $word_ary)
		{
			$title_match = ($word_in == 'title') ? 1 : 0;

			if (sizeof($word_ary))
			{
				$sql = 'INSERT INTO ' . BLOG_SEARCH_WORDMATCH_TABLE . ' (blog_id, reply_id, word_id, title_match)
					SELECT ' . $blog_id . ', ' . $reply_id . ', word_id, ' . (int) $title_match . '
					FROM ' . BLOG_SEARCH_WORDLIST_TABLE . '
					WHERE ' . $db->sql_in_set('word_text', $word_ary);
				$db->sql_query($sql);

				$sql = 'UPDATE ' . BLOG_SEARCH_WORDLIST_TABLE . '
					SET word_count = word_count + 1
					WHERE ' . $db->sql_in_set('word_text', $word_ary);
				$db->sql_query($sql);
			}
		}
		$db->sql_return_on_error(false);

		$db->sql_transaction('commit');

		// destroy cached search results containing any of the words removed or added
		$this->destroy_cache(array_unique(array_merge($words['add']['post'], $words['add']['title'], $words['del']['post'], $words['del']['title'])), array($poster_id));

		unset($unique_add_words);
		unset($words);
		unset($cur_words);
	}

	/**
	* Removes entries from the wordmatch table for the specified post_ids
	*/
	function index_remove($blog_id, $reply_id = 0)
	{
		global $db;

		$sql = 'SELECT w.word_id, w.word_text, m.title_match
			FROM ' . BLOG_SEARCH_WORDMATCH_TABLE . ' m, ' . BLOG_SEARCH_WORDLIST_TABLE . ' w
				WHERE blog_id = \'' . $blog_id . '\'
				AND reply_id = \'' . $reply_id . '\'
				AND w.word_id = m.word_id';
		$result = $db->sql_query($sql);

		$message_word_ids = $title_word_ids = $word_texts = array();
		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['title_match'])
			{
				$title_word_ids[] = $row['word_id'];
			}
			else
			{
				$message_word_ids[] = $row['word_id'];
			}
			$word_texts[] = $row['word_text'];
		}
		$db->sql_freeresult($result);

		if (sizeof($title_word_ids))
		{
			$sql = 'UPDATE ' . BLOG_SEARCH_WORDLIST_TABLE . '
				SET word_count = word_count - 1
				WHERE ' . $db->sql_in_set('word_id', $title_word_ids) . '
					AND word_count > 0';
			$db->sql_query($sql);
		}

		if (sizeof($message_word_ids))
		{
			$sql = 'UPDATE ' . BLOG_SEARCH_WORDLIST_TABLE . '
				SET word_count = word_count - 1
				WHERE ' . $db->sql_in_set('word_id', $message_word_ids) . '
					AND word_count > 0';
			$db->sql_query($sql);
		}

		unset($title_word_ids);
		unset($message_word_ids);

		$sql = 'DELETE FROM ' . BLOG_SEARCH_WORDMATCH_TABLE . '
			WHERE blog_id = \'' . $blog_id . '\'
			AND reply_id = \'' . $reply_id . '\'';
		$db->sql_query($sql);
	}

	/**
	* Deletes all words from the index
	*/
	function delete_index($acp_module = '', $u_action = '')
	{
		global $db;

		switch ($db->sql_layer)
		{
			case 'sqlite':
			case 'firebird':
				$db->sql_query('DELETE FROM ' . BLOG_SEARCH_WORDLIST_TABLE);
				$db->sql_query('DELETE FROM ' . BLOG_SEARCH_WORDMATCH_TABLE);
				//$db->sql_query('DELETE FROM ' . BLOG_SEARCH_RESULTS_TABLE);
			break;

			default:
				$db->sql_query('TRUNCATE TABLE ' . BLOG_SEARCH_WORDLIST_TABLE);
				$db->sql_query('TRUNCATE TABLE ' . BLOG_SEARCH_WORDMATCH_TABLE);
				//$db->sql_query('TRUNCATE TABLE ' . BLOG_SEARCH_RESULTS_TABLE);
			break;
		}
	}

	/**
	* Returns true if both FULLTEXT indexes exist
	*/
	function index_created()
	{
		if (!sizeof($this->stats))
		{
			$this->get_stats();
		}

		return ($this->stats['total_words'] && $this->stats['total_matches']) ? true : false;
	}

	/**
	* Returns an associative array containing information about the indexes
	*/
	function index_stats()
	{
		global $user;

		if (!sizeof($this->stats))
		{
			$this->get_stats();
		}

		return array(
			$user->lang['TOTAL_WORDS']		=> $this->stats['total_words'],
			$user->lang['TOTAL_MATCHES']	=> $this->stats['total_matches']);
	}

	function get_stats()
	{
		global $db;

		$sql = 'SELECT COUNT(*) as total_words
			FROM ' . BLOG_SEARCH_WORDLIST_TABLE;
		$result = $db->sql_query($sql);
		$this->stats['total_words'] = (int) $db->sql_fetchfield('total_words');
		$db->sql_freeresult($result);

		$sql = 'SELECT COUNT(*) as total_matches
			FROM ' . BLOG_SEARCH_WORDMATCH_TABLE;
		$result = $db->sql_query($sql);
		$this->stats['total_matches'] = (int) $db->sql_fetchfield('total_matches');
		$db->sql_freeresult($result);
	}

	/**
	* Clean up a text to remove non-alphanumeric characters
	*
	* This method receives a UTF-8 string, normalizes and validates it, replaces all
	* non-alphanumeric characters with strings then returns the result.
	*
	* Any number of "allowed chars" can be passed as a UTF-8 string in NFC.
	*
	* @param	string	$text			Text to split, in UTF-8 (not normalized or sanitized)
	* @param	string	$allowed_chars	String of special chars to allow
	* @param	string	$encoding		Text encoding
	* @return	string					Cleaned up text, only alphanumeric chars are left
	*
	* @todo normalizer::cleanup being able to be used?
	*/
	function cleanup($text, $allowed_chars = null, $encoding = 'utf-8')
	{
		global $phpbb_root_path, $phpEx;
		static $conv = array(), $conv_loaded = array();
		$words = $allow = array();

		// Convert the text to UTF-8
		$encoding = strtolower($encoding);
		if ($encoding != 'utf-8')
		{
			$text = utf8_recode($text, $encoding);
		}

		$utf_len_mask = array(
			"\xC0"	=>	2,
			"\xD0"	=>	2,
			"\xE0"	=>	3,
			"\xF0"	=>	4
		);

		/**
		* Replace HTML entities and NCRs
		*/
		$text = htmlspecialchars_decode(utf8_decode_ncr($text), ENT_QUOTES);

		/**
		* Load the UTF-8 normalizer
		*
		* If we use it more widely, an instance of that class should be held in a
		* a global variable instead
		*/
		utf_normalizer::nfc($text);

		/**
		* The first thing we do is:
		*
		* - convert ASCII-7 letters to lowercase
		* - remove the ASCII-7 non-alpha characters
		* - remove the bytes that should not appear in a valid UTF-8 string: 0xC0,
		*   0xC1 and 0xF5-0xFF
		*
		* @todo in theory, the third one is already taken care of during normalization and those chars should have been replaced by Unicode replacement chars
		*/
		$sb_match	= "ISTCPAMELRDOJBNHFGVWUQKYXZ\r\n\t!\"#$%&'()*+,-./:;<=>?@[\\]^_`{|}~\x00\x01\x02\x03\x04\x05\x06\x07\x08\x0B\x0C\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F\xC0\xC1\xF5\xF6\xF7\xF8\xF9\xFA\xFB\xFC\xFD\xFE\xFF";
		$sb_replace	= 'istcpamelrdojbnhfgvwuqkyxz                                                                              ';

		/**
		* This is the list of legal ASCII chars, it is automatically extended
		* with ASCII chars from $allowed_chars
		*/
		$legal_ascii = ' eaisntroludcpmghbfvq10xy2j9kw354867z';

		/**
		* Prepare an array containing the extra chars to allow
		*/
		if (isset($allowed_chars[0]))
		{
			$pos = 0;
			$len = strlen($allowed_chars);
			do
			{
				$c = $allowed_chars[$pos];

				if ($c < "\x80")
				{
					/**
					* ASCII char
					*/
					$sb_pos = strpos($sb_match, $c);
					if (is_int($sb_pos))
					{
						/**
						* Remove the char from $sb_match and its corresponding
						* replacement in $sb_replace
						*/
						$sb_match = substr($sb_match, 0, $sb_pos) . substr($sb_match, $sb_pos + 1);
						$sb_replace = substr($sb_replace, 0, $sb_pos) . substr($sb_replace, $sb_pos + 1);
						$legal_ascii .= $c;
					}

					++$pos;
				}
				else
				{
					/**
					* UTF-8 char
					*/
					$utf_len = $utf_len_mask[$c & "\xF0"];
					$allow[substr($allowed_chars, $pos, $utf_len)] = 1;
					$pos += $utf_len;
				}
			}
			while ($pos < $len);
		}

		$text = strtr($text, $sb_match, $sb_replace);
		$ret = '';

		$pos = 0;
		$len = strlen($text);

		do
		{
			/**
			* Do all consecutive ASCII chars at once
			*/
			if ($spn = strspn($text, $legal_ascii, $pos))
			{
				$ret .= substr($text, $pos, $spn);
				$pos += $spn;
			}

			if ($pos >= $len)
			{
				return $ret;
			}

			/**
			* Capture the UTF char
			*/
			$utf_len = $utf_len_mask[$text[$pos] & "\xF0"];
			$utf_char = substr($text, $pos, $utf_len);
			$pos += $utf_len;

			if (($utf_char >= UTF8_HANGUL_FIRST && $utf_char <= UTF8_HANGUL_LAST)
			 || ($utf_char >= UTF8_CJK_FIRST && $utf_char <= UTF8_CJK_LAST)
			 || ($utf_char >= UTF8_CJK_B_FIRST && $utf_char <= UTF8_CJK_B_LAST))
			{
				/**
				* All characters within these ranges are valid
				*
				* We separate them with a space in order to index each character
				* individually
				*/
				$ret .= ' ' . $utf_char . ' ';
				continue;
			}

			if (isset($allow[$utf_char]))
			{
				/**
				* The char is explicitly allowed
				*/
				$ret .= $utf_char;
				continue;
			}

			if (isset($conv[$utf_char]))
			{
				/**
				* The char is mapped to something, maybe to itself actually
				*/
				$ret .= $conv[$utf_char];
				continue;
			}

			/**
			* The char isn't mapped, but did we load its conversion table?
			*
			* The search indexer table is split into blocks. The block number of
			* each char is equal to its codepoint right-shifted for 11 bits. It
			* means that out of the 11, 16 or 21 meaningful bits of a 2-, 3- or
			* 4- byte sequence we only keep the leftmost 0, 5 or 10 bits. Thus,
			* all UTF chars encoded in 2 bytes are in the same first block.
			*/
			if (isset($utf_char[2]))
			{
				if (isset($utf_char[3]))
				{
					/**
					* 1111 0nnn 10nn nnnn 10nx xxxx 10xx xxxx
					* 0000 0111 0011 1111 0010 0000
					*/
					$idx = ((ord($utf_char[0]) & 0x07) << 7) | ((ord($utf_char[1]) & 0x3F) << 1) | ((ord($utf_char[2]) & 0x20) >> 5);
				}
				else
				{
					/**
					* 1110 nnnn 10nx xxxx 10xx xxxx
					* 0000 0111 0010 0000
					*/
					$idx = ((ord($utf_char[0]) & 0x07) << 1) | ((ord($utf_char[1]) & 0x20) >> 5);
				}
			}
			else
			{
				/**
				* 110x xxxx 10xx xxxx
				* 0000 0000 0000 0000
				*/
				$idx = 0;
			}

			/**
			* Check if the required conv table has been loaded already
			*/
			if (!isset($conv_loaded[$idx]))
			{
				$conv_loaded[$idx] = 1;
				$file = $phpbb_root_path . 'includes/utf/data/search_indexer_' . $idx . '.' . $phpEx;

				if (file_exists($file))
				{
					$conv += include($file);
				}
			}

			if (isset($conv[$utf_char]))
			{
				$ret .= $conv[$utf_char];
			}
			else
			{
				/**
				* We add an entry to the conversion table so that we
				* don't have to convert to codepoint and perform the checks
				* that are above this block
				*/
				$conv[$utf_char] = ' ';
				$ret .= ' ';
			}
		}
		while (1);

		return $ret;
	}
}

?>