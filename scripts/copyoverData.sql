DECLARE @sourceTable as varchar(200)
DECLARE @targetTable as varchar(200)

SET @sourceTable = 'XCP_TEST_PRD'
SET @targetTable = 'XCP_TEST_DEV'

-- ACT_AUDIT

EXEC('	TRUNCATE TABLE '+@targetTable+'.dbo.ACT_AUDIT;
		SET IDENTITY_INSERT '+@targetTable+'.dbo.ACT_AUDIT ON; 
		INSERT INTO '+@targetTable+'.dbo.ACT_AUDIT ([ID],[XCPID],[ACT],[STATUS],[DATE],[USER_ID],[DATA],[allocatedTo],[allocatedOn],[allocatedBy]) SELECT [ID],[XCPID],[ACT],[STATUS],[DATE],[USER_ID],[DATA],[allocatedTo],[allocatedOn],[allocatedBy] FROM '+@sourceTable+'.dbo.ACT_AUDIT;
		SET IDENTITY_INSERT '+@targetTable+'.dbo.ACT_AUDIT OFF')
PRINT 'COPIED: '+@sourceTable+'.ACT_AUDIT -> '+@targetTable+ '.ACT_AUDIT'

-- ACT_DETAIL
EXEC('	TRUNCATE TABLE '+@targetTable+'.dbo.[ACT_DETAIL];
		INSERT INTO '+@targetTable+'.dbo.[ACT_DETAIL] SELECT * FROM '+@sourceTable+'.dbo.[ACT_DETAIL]')
PRINT 'COPIED: ACT_DETAIL'

-- ACT_MAPPING
TRUNCATE TABLE XCP_TEST_DEV.dbo.ACT_MAPPING
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.ACT_MAPPING ON
INSERT INTO XCP_TEST_DEV.dbo.[ACT_MAPPING] ([act_in],[status_in],[act_out],[status_out],[id],[assign],[set_id],[action_id]) SELECT [act_in],[status_in],[act_out],[status_out],[id],[assign],[set_id],[action_id] FROM XCP_TEST_PRD.dbo.[ACT_MAPPING]
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.ACT_MAPPING OFF
PRINT 'COPIED: ACT_MAPPING'

-- ACT_MAPPING_LINK
TRUNCATE TABLE XCP_TEST_DEV.dbo.ACT_MAPPING_LINK
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.ACT_MAPPING_LINK ON
INSERT INTO XCP_TEST_DEV.dbo.ACT_MAPPING_LINK ([id],[pipeline_id],[mapping_set_id]) SELECT [id],[pipeline_id],[mapping_set_id] FROM XCP_TEST_PRD.dbo.ACT_MAPPING_LINK
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.ACT_MAPPING_LINK OFF
PRINT 'COPIED: ACT_MAPPING_LINK'

-- ACT_STATUS
TRUNCATE TABLE XCP_TEST_DEV.dbo.ACT_STATUS
INSERT INTO XCP_TEST_DEV.dbo.ACT_STATUS SELECT * FROM XCP_TEST_PRD.dbo.ACT_STATUS
PRINT 'COPIED: ACT_STATUS'

-- ACT_STATUS_2
TRUNCATE TABLE XCP_TEST_DEV.dbo.ACT_STATUS_2
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.ACT_STATUS_2 ON
INSERT INTO XCP_TEST_DEV.dbo.ACT_STATUS_2 ([id],[act],[status],[name],[description]) SELECT [id],[act],[status],[name],[description] FROM XCP_TEST_PRD.dbo.ACT_STATUS_2
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.ACT_STATUS_2 OFF
PRINT 'COPIED: ACT_STATUS_2'

-- ACTION_FIELDS
TRUNCATE TABLE XCP_TEST_DEV.dbo.ACTION_FIELDS
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.ACTION_FIELDS ON
INSERT INTO XCP_TEST_DEV.dbo.ACTION_FIELDS ([field_id],[action_id],[field_name],[field_name_display],[field_prefix],[field_suffix],[data_required],[data_child_of],[data_type],[data_validation],[data_placeholder],[data_validation_helper],[source_table],[source_prefill]) SELECT [field_id],[action_id],[field_name],[field_name_display],[field_prefix],[field_suffix],[data_required],[data_child_of],[data_type],[data_validation],[data_placeholder],[data_validation_helper],[source_table],[source_prefill] FROM XCP_TEST_PRD.dbo.ACTION_FIELDS
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.ACTION_FIELDS OFF
PRINT 'COPIED: ACTION_FIELDS'

-- ACTION_LIST
TRUNCATE TABLE XCP_TEST_DEV.dbo.ACTION_LIST
INSERT INTO XCP_TEST_DEV.dbo.ACTION_LIST SELECT * FROM XCP_TEST_PRD.dbo.ACTION_LIST
PRINT 'COPIED: ACTION_LIST'

-- DWH_DATA
TRUNCATE TABLE XCP_TEST_DEV.dbo.DWH_DATA
INSERT INTO XCP_TEST_DEV.dbo.DWH_DATA SELECT * FROM XCP_TEST_PRD.dbo.DWH_DATA
PRINT 'COPIED: DWH_DATA'

-- DWH_VALIDATION
TRUNCATE TABLE XCP_TEST_DEV.dbo.DWH_VALIDATION
INSERT INTO XCP_TEST_DEV.dbo.DWH_VALIDATION SELECT * FROM XCP_TEST_PRD.dbo.DWH_VALIDATION
PRINT 'COPIED: DWH_VALIDATION'

-- DWH_VALIDATION_LINK
TRUNCATE TABLE XCP_TEST_DEV.dbo.DWH_VALIDATION_LINK
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.DWH_VALIDATION_LINK ON
INSERT INTO XCP_TEST_DEV.dbo.DWH_VALIDATION_LINK ([link_id],[validation_id],[feed_id]) SELECT [link_id],[validation_id],[feed_id] FROM XCP_TEST_PRD.dbo.DWH_VALIDATION_LINK
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.DWH_VALIDATION_LINK OFF
PRINT 'COPIED: DWH_VALIDATION_LINK'

-- DWH_VALIDATION_RULES
TRUNCATE TABLE XCP_TEST_DEV.dbo.DWH_VALIDATION_RULES
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.DWH_VALIDATION_RULES ON
INSERT INTO XCP_TEST_DEV.dbo.DWH_VALIDATION_RULES ([id],[field],[required],[validation]) SELECT [id],[field],[required],[validation] FROM XCP_TEST_PRD.dbo.DWH_VALIDATION_RULES
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.DWH_VALIDATION_RULES OFF
PRINT 'COPIED: DWH_VALIDATION_RULES'

-- ERROR_LOOKUP
TRUNCATE TABLE XCP_TEST_DEV.dbo.ERROR_LOOKUP
INSERT INTO XCP_TEST_DEV.dbo.ERROR_LOOKUP SELECT * FROM XCP_TEST_PRD.dbo.ERROR_LOOKUP
PRINT 'COPIED: ERROR_LOOKUP'

-- FEED_DATA
TRUNCATE TABLE XCP_TEST_DEV.dbo.FEED_DATA
INSERT INTO XCP_TEST_DEV.dbo.FEED_DATA SELECT * FROM XCP_TEST_PRD.dbo.FEED_DATA
PRINT 'COPIED: FEED_DATA'

-- FEED_EXCLUTION
TRUNCATE TABLE XCP_TEST_DEV.dbo.FEED_EXCLUTION
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.FEED_EXCLUTION ON
INSERT INTO XCP_TEST_DEV.dbo.FEED_EXCLUTION ([EXCLUTION_ID],[UPI],[FEED_ID],[DT_ADDED],[USER_ID],[COMMENT]) SELECT [EXCLUTION_ID],[UPI],[FEED_ID],[DT_ADDED],[USER_ID],[COMMENT] FROM XCP_TEST_PRD.dbo.FEED_EXCLUTION
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.FEED_EXCLUTION OFF
PRINT 'COPIED: FEED_EXCLUTION'

-- FEEDS
TRUNCATE TABLE XCP_TEST_DEV.dbo.FEEDS
INSERT INTO XCP_TEST_DEV.dbo.FEEDS SELECT * FROM XCP_TEST_PRD.dbo.FEEDS
PRINT 'COPIED: FEEDS'

-- FILE_COLLATION
TRUNCATE TABLE XCP_TEST_DEV.dbo.FILE_COLLATION
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.FILE_COLLATION ON
INSERT INTO XCP_TEST_DEV.dbo.FILE_COLLATION ([ID],[XCP_ID],[FILE_NAME],[FILE_LOCATION],[STATUS],[COLLATION_DATE]) SELECT [ID],[XCP_ID],[FILE_NAME],[FILE_LOCATION],[STATUS],[COLLATION_DATE] FROM XCP_TEST_PRD.dbo.FILE_COLLATION
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.FILE_COLLATION OFF
PRINT 'COPIED: FILE_COLLATION'

-- foundTest
TRUNCATE TABLE XCP_TEST_DEV.dbo.foundTest
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.foundTest ON
INSERT INTO XCP_TEST_DEV.dbo.foundTest ([id],[XCPID],[lookFor],[found]) SELECT [id],[XCPID],[lookFor],[found] FROM XCP_TEST_PRD.dbo.foundTest
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.foundTest OFF
PRINT 'COPIED: foundTest'

-- GROUPS
TRUNCATE TABLE XCP_TEST_DEV.dbo.GROUPS
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.GROUPS ON
INSERT INTO XCP_TEST_DEV.dbo.GROUPS ([id],[name],[permissions]) SELECT [id],[name],[permissions] FROM XCP_TEST_PRD.dbo.GROUPS
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.GROUPS OFF
PRINT 'COPIED: GROUPS'

-- INIT_STATUS
TRUNCATE TABLE XCP_TEST_DEV.dbo.INIT_STATUS
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.INIT_STATUS ON
INSERT INTO XCP_TEST_DEV.dbo.INIT_STATUS ([id],[jobName],[start_dt],[end_dt],[data]) SELECT [id],[jobName],[start_dt],[end_dt],[data] FROM XCP_TEST_PRD.dbo.INIT_STATUS
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.INIT_STATUS OFF
PRINT 'COPIED: INIT_STATUS'

-- ITEM_DATA
TRUNCATE TABLE XCP_TEST_DEV.dbo.ITEM_DATA
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.ITEM_DATA ON
INSERT INTO XCP_TEST_DEV.dbo.ITEM_DATA ([id],[xcpid],[data_key],[data_value],[data_type],[created_on],[created_by],[edited_on],[edited_by]) SELECT [id],[xcpid],[data_key],[data_value],[data_type],[created_on],[created_by],[edited_on],[edited_by] FROM XCP_TEST_PRD.dbo.ITEM_DATA
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.ITEM_DATA OFF
PRINT 'COPIED: ITEM_DATA'

-- PIPELINE_MAPPING
TRUNCATE TABLE XCP_TEST_DEV.dbo.PIPELINE_MAPPING
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.PIPELINE_MAPPING ON
INSERT INTO XCP_TEST_DEV.dbo.PIPELINE_MAPPING ([id],[stream_id],[pipeline_id]) SELECT [id],[stream_id],[pipeline_id] FROM XCP_TEST_PRD.dbo.PIPELINE_MAPPING
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.PIPELINE_MAPPING OFF
PRINT 'COPIED: PIPELINE_MAPPING'

-- STREAM_ALLOCATION
TRUNCATE TABLE XCP_TEST_DEV.dbo.STREAM_ALLOCATION
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.STREAM_ALLOCATION ON
INSERT INTO XCP_TEST_DEV.dbo.STREAM_ALLOCATION ([ID],[XCP_ID],[STREAM_ID],[ALLOCATION_DATE]) SELECT [ID],[XCP_ID],[STREAM_ID],[ALLOCATION_DATE] FROM XCP_TEST_PRD.dbo.STREAM_ALLOCATION
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.STREAM_ALLOCATION OFF
PRINT 'COPIED: STREAM_ALLOCATION'

-- STREAM_DETAILS
TRUNCATE TABLE XCP_TEST_DEV.dbo.STREAM_DETAILS
INSERT INTO XCP_TEST_DEV.dbo.STREAM_DETAILS SELECT * FROM XCP_TEST_PRD.dbo.STREAM_DETAILS
PRINT 'COPIED: STREAM_DETAILS'

-- USERS
TRUNCATE TABLE XCP_TEST_DEV.dbo.USERS
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.USERS ON
INSERT INTO XCP_TEST_DEV.dbo.USERS ([id],[username],[password],[salt],[name_first],[name_last],[joined],[group_id]) SELECT [id],[username],[password],[salt],[name_first],[name_last],[joined],[group_id] FROM XCP_TEST_PRD.dbo.USERS
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.USERS OFF
PRINT 'COPIED: USERS'

-- USERS_SESSION
TRUNCATE TABLE XCP_TEST_DEV.dbo.USERS_SESSION
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.USERS_SESSION ON
INSERT INTO XCP_TEST_DEV.dbo.USERS_SESSION ([id],[user_id],[hash]) SELECT [id],[user_id],[hash] FROM XCP_TEST_PRD.dbo.USERS_SESSION
SET IDENTITY_INSERT XCP_TEST_DEV.dbo.USERS_SESSION OFF
PRINT 'COPIED: USERS_SESSION'
