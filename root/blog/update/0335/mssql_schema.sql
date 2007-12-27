/*
User Blogs Mod Database Schema
*/

BEGIN TRANSACTION
GO

/*
	Table: 'phpbb_blogs_ratings'
*/
CREATE TABLE [phpbb_blogs_ratings] (
	[blog_id] [int] DEFAULT (0) NOT NULL ,
	[user_id] [int] DEFAULT (0) NOT NULL ,
	[rating] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY]
GO

ALTER TABLE [phpbb_blogs_ratings] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_blogs_ratings] PRIMARY KEY  CLUSTERED 
	(
		[blog_id],
		[user_id]
	)  ON [PRIMARY] 
GO



COMMIT
GO

