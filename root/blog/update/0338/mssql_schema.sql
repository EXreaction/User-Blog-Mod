/*

 $Id: $

*/

BEGIN TRANSACTION
GO

/*
	Table: 'phpbb_blogs_attachment'
*/
CREATE TABLE [phpbb_blogs_attachment] (
	[attach_id] [int] IDENTITY (1, 1) NOT NULL ,
	[blog_id] [int] DEFAULT (0) NOT NULL ,
	[reply_id] [int] DEFAULT (0) NOT NULL ,
	[poster_id] [int] DEFAULT (0) NOT NULL ,
	[is_orphan] [int] DEFAULT (1) NOT NULL ,
	[physical_filename] [varchar] (255) DEFAULT ('') NOT NULL ,
	[real_filename] [varchar] (255) DEFAULT ('') NOT NULL ,
	[download_count] [int] DEFAULT (0) NOT NULL ,
	[attach_comment] [varchar] (4000) DEFAULT ('') NOT NULL ,
	[extension] [varchar] (100) DEFAULT ('') NOT NULL ,
	[mimetype] [varchar] (100) DEFAULT ('') NOT NULL ,
	[filesize] [int] DEFAULT (0) NOT NULL ,
	[filetime] [int] DEFAULT (0) NOT NULL ,
	[thumbnail] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY]
GO

ALTER TABLE [phpbb_blogs_attachment] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_blogs_attachment] PRIMARY KEY  CLUSTERED 
	(
		[attach_id]
	)  ON [PRIMARY] 
GO

CREATE  INDEX [blog_id] ON [phpbb_blogs_attachment]([blog_id]) ON [PRIMARY]
GO

CREATE  INDEX [reply_id] ON [phpbb_blogs_attachment]([reply_id]) ON [PRIMARY]
GO

CREATE  INDEX [filetime] ON [phpbb_blogs_attachment]([filetime]) ON [PRIMARY]
GO

CREATE  INDEX [poster_id] ON [phpbb_blogs_attachment]([poster_id]) ON [PRIMARY]
GO

CREATE  INDEX [is_orphan] ON [phpbb_blogs_attachment]([is_orphan]) ON [PRIMARY]
GO



COMMIT
GO

