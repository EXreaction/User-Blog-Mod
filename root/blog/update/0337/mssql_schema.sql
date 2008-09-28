BEGIN TRANSACTION
GO

/*
	Table: 'phpbb_blogs_poll_options'
*/
CREATE TABLE [phpbb_blogs_poll_options] (
	[poll_option_id] [int] DEFAULT (0) NOT NULL ,
	[blog_id] [int] DEFAULT (0) NOT NULL ,
	[poll_option_text] [varchar] (4000) DEFAULT ('') NOT NULL ,
	[poll_option_total] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY]
GO

CREATE  INDEX [poll_opt_id] ON [phpbb_blogs_poll_options]([poll_option_id]) ON [PRIMARY]
GO

CREATE  INDEX [blog_id] ON [phpbb_blogs_poll_options]([blog_id]) ON [PRIMARY]
GO


/*
	Table: 'phpbb_blogs_poll_votes'
*/
CREATE TABLE [phpbb_blogs_poll_votes] (
	[blog_id] [int] DEFAULT (0) NOT NULL ,
	[poll_option_id] [int] DEFAULT (0) NOT NULL ,
	[vote_user_id] [int] DEFAULT (0) NOT NULL ,
	[vote_user_ip] [varchar] (40) DEFAULT ('') NOT NULL 
) ON [PRIMARY]
GO

CREATE  INDEX [blog_id] ON [phpbb_blogs_poll_votes]([blog_id]) ON [PRIMARY]
GO

CREATE  INDEX [vote_user_id] ON [phpbb_blogs_poll_votes]([vote_user_id]) ON [PRIMARY]
GO

CREATE  INDEX [vote_user_ip] ON [phpbb_blogs_poll_votes]([vote_user_ip]) ON [PRIMARY]
GO



COMMIT
GO

