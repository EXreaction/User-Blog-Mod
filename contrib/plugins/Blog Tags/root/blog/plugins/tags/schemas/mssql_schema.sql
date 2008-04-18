/*

 $Id: $

*/

BEGIN TRANSACTION
GO

/*
	Table: 'phpbb_blogs_tags'
*/
CREATE TABLE [phpbb_blogs_tags] (
	[tag_id] [int] IDENTITY (1, 1) NOT NULL ,
	[tag_name] [text] DEFAULT ('') NOT NULL ,
	[tag_count] [int] DEFAULT (0) NOT NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [phpbb_blogs_tags] WITH NOCHECK ADD 
	CONSTRAINT [PK_phpbb_blogs_tags] PRIMARY KEY  CLUSTERED 
	(
		[tag_id]
	)  ON [PRIMARY] 
GO

CREATE  INDEX [tag_count] ON [phpbb_blogs_tags]([tag_count]) ON [PRIMARY]
GO



COMMIT
GO

