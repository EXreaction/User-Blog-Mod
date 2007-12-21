/*
User Blogs Mod Database Schema
*/

BEGIN TRANSACTION
GO

/*
	Table: 'phpbb_blogs_categories'
*/
CREATE TABLE [phpbb_blogs_categories] (
	[category_id] [int] IDENTITY (1, 1) NOT NULL ,
	[parent_id] [int] DEFAULT (0) NOT NULL ,
	[left_id] [int] DEFAULT (0) NOT NULL ,
	[right_id] [int] DEFAULT (0) NOT NULL ,
	[category_name] [varchar] (255) DEFAULT ('') NOT NULL ,
	[category_description] [text] DEFAULT ('') NOT NULL ,
	[category_description_bitfield] [varchar] (255) DEFAULT ('') NOT NULL ,
	[category_description_uid] [varchar] (8) DEFAULT ('') NOT NULL ,
	[category_description_options] [int] DEFAULT (7) NOT NULL ,
	[rules] [text] DEFAULT ('') NOT NULL ,
	[rules_bitfield] [varchar] (255) DEFAULT ('') NOT NULL ,
	[rules_uid] [varchar] (8) DEFAULT ('') NOT NULL ,
	[rules_options] [int] DEFAULT (7) NOT NULL ,
	[blog_count] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [phpbb_blogs_categories] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_blogs_categories] PRIMARY KEY  CLUSTERED 
	(
		[category_id]
	)  ON [PRIMARY] 
GO

CREATE  INDEX [left_right_id] ON [phpbb_blogs_categories]([left_id], [right_id]) ON [PRIMARY]
GO


/*
	Table: 'phpbb_blogs_in_categories'
*/
CREATE TABLE [phpbb_blogs_in_categories] (
	[blog_id] [int] DEFAULT (0) NOT NULL ,
	[category_id] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY]
GO

ALTER TABLE [phpbb_blogs_in_categories] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_blogs_in_categories] PRIMARY KEY  CLUSTERED 
	(
		[blog_id],
		[category_id]
	)  ON [PRIMARY] 
GO



COMMIT
GO

