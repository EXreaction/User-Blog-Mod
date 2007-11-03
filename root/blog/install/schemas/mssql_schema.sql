/*
User Blogs Mod Database Schema
*/

BEGIN TRANSACTION
GO

/*
	Table: 'phpbb_blogs'
*/
CREATE TABLE [phpbb_blogs] (
	[blog_id] [int] IDENTITY (1, 1) NOT NULL ,
	[user_id] [int] DEFAULT (0) NOT NULL ,
	[user_ip] [varchar] (40) DEFAULT ('') NOT NULL ,
	[blog_subject] [varchar] (100) DEFAULT ('') NOT NULL ,
	[blog_text] [text] DEFAULT ('') NOT NULL ,
	[blog_checksum] [varchar] (32) DEFAULT ('') NOT NULL ,
	[blog_time] [int] DEFAULT (0) NOT NULL ,
	[blog_approved] [int] DEFAULT (1) NOT NULL ,
	[blog_reported] [int] DEFAULT (0) NOT NULL ,
	[enable_bbcode] [int] DEFAULT (1) NOT NULL ,
	[enable_smilies] [int] DEFAULT (1) NOT NULL ,
	[enable_magic_url] [int] DEFAULT (1) NOT NULL ,
	[bbcode_bitfield] [varchar] (255) DEFAULT ('') NOT NULL ,
	[bbcode_uid] [varchar] (8) DEFAULT ('') NOT NULL ,
	[blog_edit_time] [int] DEFAULT (0) NOT NULL ,
	[blog_edit_reason] [varchar] (255) DEFAULT ('') NOT NULL ,
	[blog_edit_user] [int] DEFAULT (0) NOT NULL ,
	[blog_edit_count] [int] DEFAULT (0) NOT NULL ,
	[blog_edit_locked] [int] DEFAULT (0) NOT NULL ,
	[blog_deleted] [int] DEFAULT (0) NOT NULL ,
	[blog_deleted_time] [int] DEFAULT (0) NOT NULL ,
	[blog_read_count] [int] DEFAULT (0) NOT NULL ,
	[blog_reply_count] [int] DEFAULT (0) NOT NULL ,
	[blog_real_reply_count] [int] DEFAULT (0) NOT NULL ,
	[perm_guest] [int] DEFAULT (1) NOT NULL ,
	[perm_registered] [int] DEFAULT (2) NOT NULL ,
	[perm_foe] [int] DEFAULT (0) NOT NULL ,
	[perm_friend] [int] DEFAULT (2) NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [phpbb_blogs] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_blogs] PRIMARY KEY  CLUSTERED 
	(
		[blog_id]
	)  ON [PRIMARY] 
GO

CREATE  INDEX [user_id] ON [phpbb_blogs]([user_id]) ON [PRIMARY]
GO

CREATE  INDEX [user_ip] ON [phpbb_blogs]([user_ip]) ON [PRIMARY]
GO

CREATE  INDEX [blog_approved] ON [phpbb_blogs]([blog_approved]) ON [PRIMARY]
GO

CREATE  INDEX [blog_deleted] ON [phpbb_blogs]([blog_deleted]) ON [PRIMARY]
GO

CREATE  INDEX [perm_guest] ON [phpbb_blogs]([perm_guest]) ON [PRIMARY]
GO

CREATE  INDEX [perm_registered] ON [phpbb_blogs]([perm_registered]) ON [PRIMARY]
GO

CREATE  INDEX [perm_foe] ON [phpbb_blogs]([perm_foe]) ON [PRIMARY]
GO

CREATE  INDEX [perm_friend] ON [phpbb_blogs]([perm_friend]) ON [PRIMARY]
GO


/*
	Table: 'phpbb_blogs_reply'
*/
CREATE TABLE [phpbb_blogs_reply] (
	[reply_id] [int] IDENTITY (1, 1) NOT NULL ,
	[blog_id] [int] DEFAULT (0) NOT NULL ,
	[user_id] [int] DEFAULT (0) NOT NULL ,
	[user_ip] [varchar] (40) DEFAULT ('') NOT NULL ,
	[reply_subject] [varchar] (100) DEFAULT ('') NOT NULL ,
	[reply_text] [text] DEFAULT ('') NOT NULL ,
	[reply_checksum] [varchar] (32) DEFAULT ('') NOT NULL ,
	[reply_time] [int] DEFAULT (0) NOT NULL ,
	[reply_approved] [int] DEFAULT (1) NOT NULL ,
	[reply_reported] [int] DEFAULT (0) NOT NULL ,
	[enable_bbcode] [int] DEFAULT (1) NOT NULL ,
	[enable_smilies] [int] DEFAULT (1) NOT NULL ,
	[enable_magic_url] [int] DEFAULT (1) NOT NULL ,
	[bbcode_bitfield] [varchar] (255) DEFAULT ('') NOT NULL ,
	[bbcode_uid] [varchar] (8) DEFAULT ('') NOT NULL ,
	[reply_edit_time] [int] DEFAULT (0) NOT NULL ,
	[reply_edit_reason] [varchar] (255) DEFAULT ('') NOT NULL ,
	[reply_edit_user] [int] DEFAULT (0) NOT NULL ,
	[reply_edit_count] [int] DEFAULT (0) NOT NULL ,
	[reply_edit_locked] [int] DEFAULT (0) NOT NULL ,
	[reply_deleted] [int] DEFAULT (0) NOT NULL ,
	[reply_deleted_time] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [phpbb_blogs_reply] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_blogs_reply] PRIMARY KEY  CLUSTERED 
	(
		[reply_id]
	)  ON [PRIMARY] 
GO

CREATE  INDEX [user_id] ON [phpbb_blogs_reply]([user_id]) ON [PRIMARY]
GO

CREATE  INDEX [user_ip] ON [phpbb_blogs_reply]([user_ip]) ON [PRIMARY]
GO

CREATE  INDEX [reply_approved] ON [phpbb_blogs_reply]([reply_approved]) ON [PRIMARY]
GO

CREATE  INDEX [reply_deleted] ON [phpbb_blogs_reply]([reply_deleted]) ON [PRIMARY]
GO


/*
	Table: 'phpbb_blogs_subscription'
*/
CREATE TABLE [phpbb_blogs_subscription] (
	[sub_user_id] [int] DEFAULT (0) NOT NULL ,
	[sub_type] [int] DEFAULT (1) NOT NULL ,
	[blog_id] [int] DEFAULT (0) NOT NULL ,
	[user_id] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY]
GO

ALTER TABLE [phpbb_blogs_subscription] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_blogs_subscription] PRIMARY KEY  CLUSTERED 
	(
		[sub_user_id, sub_type, blog_id, user_id]
	)  ON [PRIMARY] 
GO


/*
	Table: 'phpbb_blogs_plugins'
*/
CREATE TABLE [phpbb_blogs_plugins] (
	[plugin_id] [int] IDENTITY (1, 1) NOT NULL ,
	[plugin_name] [varchar] (100) DEFAULT ('') NOT NULL ,
	[plugin_enabled] [int] DEFAULT (0) NOT NULL ,
	[plugin_version] [varchar] (100) DEFAULT ('') NOT NULL 
) ON [PRIMARY]
GO

ALTER TABLE [phpbb_blogs_plugins] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_blogs_plugins] PRIMARY KEY  CLUSTERED 
	(
		[plugin_id]
	)  ON [PRIMARY] 
GO

CREATE  INDEX [plugin_name] ON [phpbb_blogs_plugins]([plugin_name]) ON [PRIMARY]
GO

CREATE  INDEX [plugin_enabled] ON [phpbb_blogs_plugins]([plugin_enabled]) ON [PRIMARY]
GO


/*
	Table: 'phpbb_blogs_users'
*/
CREATE TABLE [phpbb_blogs_users] (
	[user_id] [int] DEFAULT (0) NOT NULL ,
	[perm_guest] [int] DEFAULT (1) NOT NULL ,
	[perm_registered] [int] DEFAULT (2) NOT NULL ,
	[perm_foe] [int] DEFAULT (0) NOT NULL ,
	[perm_friend] [int] DEFAULT (2) NOT NULL ,
	[title] [varchar] (100) DEFAULT ('') NOT NULL ,
	[description] [text] DEFAULT ('') NOT NULL ,
	[description_bbcode_bitfield] [varchar] (255) DEFAULT ('') NOT NULL ,
	[description_bbcode_uid] [varchar] (8) DEFAULT ('') NOT NULL ,
	[instant_redirect] [int] DEFAULT (1) NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [phpbb_blogs_users] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_blogs_users] PRIMARY KEY  CLUSTERED 
	(
		[user_id]
	)  ON [PRIMARY] 
GO



COMMIT
GO
