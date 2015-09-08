ALTER TABLE [dbo].[ACT_MAPPING] ADD [action_id] [int] NULL   
GO
CREATE TABLE [dbo].[ACTION_FIELDS] (
   [field_id] [int] NOT NULL  
      IDENTITY (1,1) ,
   [action_id] [int] NOT NULL   ,
   [field_name] [varchar](500) NOT NULL ,
   [field_name_display] [varchar](500) NOT NULL ,
   [field_prefix] [varchar](500) NULL ,
   [field_suffix] [varchar](500) NULL ,
   [data_required] [bit] NOT NULL   ,
   [data_child_of] [int] NULL   ,
   [data_type] [int] NOT NULL   ,
   [data_validation] [varchar](500) NULL ,
   [data_placeholder] [varchar](500) NULL ,
   [data_validation_helper] [varchar](500) NULL ,
   [source_table] [varchar](500) NOT NULL ,
   [source_prefill] [bit] NOT NULL   

   ,CONSTRAINT [PK_ACTION_FIELDS2] PRIMARY KEY CLUSTERED ([field_id], [field_name])
)

CREATE TABLE [dbo].[ACTION_LIST] (
   [action_id] [int] NOT NULL   ,
   [action_type] [int] NOT NULL   ,
   [action_name] [varchar](200) NOT NULL ,
   [action_description] [varchar](1000) NOT NULL ,
   [action_title] [varchar](1000) NULL 

   ,CONSTRAINT [PK_ACTION_LIST] PRIMARY KEY CLUSTERED ([action_id])
)

CREATE TABLE [dbo].[ITEM_DATA] (
   [id] [int] NOT NULL  
      IDENTITY (1,1) ,
   [xcpid] [nvarchar](10) NOT NULL ,
   [data_key] [varchar](500) NOT NULL ,
   [data_value] [varchar](5000) NULL ,
   [data_type] [int] NULL   ,
   [created_on] [datetime] NOT NULL   ,
   [created_by] [int] NOT NULL   ,
   [edited_on] [datetime] NULL   ,
   [edited_by] [int] NULL   
)

GO
SET QUOTED_IDENTIFIER ON 
GO
SET ANSI_NULLS ON 
GO
CREATE VIEW [allData]
AS
SELECT        dbo.feed_data.xcp_id, STUFF
                             ((SELECT        ',' + cast(pipeline_id AS varchar(2))
                                 FROM            PIPELINE_MAPPING PM
                                 WHERE        PM.stream_id = STREAM_ALLOCATION.STREAM_ID FOR XML PATH(''), TYPE ).value('.', 'varchar(max)'), 1, 1, '') AS pipeline_ids, 
                         COLLATION.file_location, dbo.feed_data.material_id, dbo.feed_data.feed_id, dbo.feeds.feed_name, dbo.feed_data.date_added, PRO.datavalue AS projectStatus, 
                         PAG.datavalue AS pageCount, SDO.datavalue AS standardsBody, ORG.datavalue AS originatingOrg, PTY.datavalue AS projectType, TIT.datavalue AS materialTitle, 
                         PNO.datavalue AS projectNumber, DSC.datavalue AS materialDescription, PFP.datavalue AS projectForecastPubl, SUP.datavalue AS supersedes, 
                         dbo.stream_allocation.stream_id, dbo.dwh_validation.validation_check, dbo.foundtest.lookfor, dbo.foundtest.found, dbo.error_lookup.error_description, 
                         dbo.dwh_validation.validation_error, CASE WHEN dbo.feed_exclution.exclution_id IS NULL THEN 1 ELSE 0 END AS INCLUDED, 
                         dbo.feed_exclution.exclution_id
FROM            dbo.feed_data LEFT OUTER JOIN
                         dbo.dwh_data AS PRO ON dbo.feed_data.material_id = PRO.material_id LEFT OUTER JOIN
                         dbo.dwh_data AS SDO ON dbo.feed_data.material_id = SDO.material_id LEFT OUTER JOIN
                         dbo.dwh_data AS ORG ON dbo.feed_data.material_id = ORG.material_id LEFT OUTER JOIN
                         dbo.dwh_data AS PTY ON dbo.feed_data.material_id = PTY.material_id LEFT OUTER JOIN
                         dbo.dwh_data AS PNO ON dbo.feed_data.material_id = PNO.material_id LEFT OUTER JOIN
                         dbo.dwh_data AS DSC ON dbo.feed_data.material_id = DSC.material_id LEFT OUTER JOIN
                         dbo.dwh_data AS SUP ON dbo.feed_data.material_id = SUP.material_id LEFT OUTER JOIN
                         dbo.dwh_data AS PAG ON dbo.feed_data.material_id = PAG.material_id LEFT OUTER JOIN
                         dbo.dwh_data AS TIT ON dbo.feed_data.material_id = TIT.material_id LEFT OUTER JOIN
                         dbo.dwh_data AS PFP ON dbo.feed_data.material_id = PFP.material_id LEFT OUTER JOIN
                         dbo.stream_allocation ON dbo.feed_data.xcp_id = dbo.stream_allocation.xcp_id LEFT OUTER JOIN
                         dbo.error_lookup ON dbo.stream_allocation.stream_id = dbo.error_lookup.error_id LEFT OUTER JOIN
                         dbo.foundtest ON dbo.foundtest.xcpid = dbo.feed_data.xcp_id LEFT OUTER JOIN
                         dbo.feeds ON dbo.feeds.feed_id = dbo.feed_data.feed_id INNER JOIN
                         dbo.dwh_validation ON dbo.feed_data.material_id = dbo.dwh_validation.upi LEFT OUTER JOIN
                         dbo.feed_exclution ON dbo.feed_data.feed_id = dbo.feed_exclution.feed_id AND dbo.feed_data.material_id = dbo.feed_exclution.upi OUTER apply
                             (SELECT        TOP 1 *
                               FROM            dbo.file_collation
                               WHERE        xcp_id = feed_data.xcp_id) collation
WHERE        (PRO.datatype = 'projectStatus') AND (SDO.datatype = 'standardsBody') AND (ORG.datatype = 'originatingOrg') AND (PTY.datatype = 'projectType') AND 
                         (PNO.datatype = 'projectNumber') AND (DSC.datatype = 'materialDescription') AND (SUP.datatype = 'supersedes') AND (PAG.datatype = 'pageCount') AND 
                         (TIT.datatype = 'materialTitle') AND (PFP.datatype = 'projectForecastPubl') AND dbo.feed_exclution.exclution_id IS NULL 


GO
SET QUOTED_IDENTIFIER OFF 
GO
SET ANSI_NULLS OFF 
GO

GO
SET QUOTED_IDENTIFIER ON 
GO
SET ANSI_NULLS ON 
GO

CREATE VIEW [auditItems]
AS
SELECT DISTINCT 
                         au.XCPID, au.act + ':' + au.status stage, cnt.iterations, fst.DATE firstDate, fst.allocatedOn firstAllocatedDate, fst.allocatedTo firstAllocatedUser, lst.DATE lastDate, 
                         lst.allocatedOn lastAllocatedDate, lst.allocatedTo lastAllocatedUser
FROM           [dbo].[ACT_AUDIT] au OUTER apply
                             (SELECT        TOP 1 *
                               FROM            ACT_AUDIT fst
                               WHERE        fst.xcpid = au.XCPID AND au.act = fst.act AND au.status = fst.status
                               ORDER BY DATE DESC) fst OUTER apply
                             (SELECT        TOP 1 *
                               FROM            ACT_AUDIT lst
                               WHERE        lst.xcpid = au.XCPID AND au.act = lst.act AND au.status = lst.status
                               ORDER BY DATE ASC) lst OUTER apply
                             (SELECT        COUNT(*) iterations
                               FROM            ACT_AUDIT cnt
                               WHERE        cnt.xcpid = au.XCPID AND au.act = cnt.act AND au.status = cnt.status) cnt




GO
SET QUOTED_IDENTIFIER OFF 
GO
SET ANSI_NULLS OFF 
GO

GO
SET QUOTED_IDENTIFIER ON 
GO
SET ANSI_NULLS ON 
GO
ALTER VIEW [mainData]
AS
SELECT        dbo.feed_data.xcp_id, STUFF
                             ((SELECT        ',' + cast(pipeline_id AS varchar(2))
                                 FROM            PIPELINE_MAPPING PM
                                 WHERE        PM.stream_id = STREAM_ALLOCATION.STREAM_ID FOR XML PATH(''), TYPE ).value('.', 'varchar(max)'), 1, 1, '') AS pipeline_ids, 
                         COLLATION.file_location, dbo.feed_data.material_id, dbo.feed_data.feed_id, dbo.feeds.feed_name, dbo.feed_data.date_added, PRO.datavalue AS projectStatus, 
                         PAG.datavalue AS pageCount, SDO.datavalue AS standardsBody, ORG.datavalue AS originatingOrg, PTY.datavalue AS projectType, TIT.datavalue AS materialTitle, 
                         PNO.datavalue AS projectNumber, DSC.datavalue AS materialDescription, PFP.datavalue AS projectForecastPubl, SUP.datavalue AS supersedes, 
                         dbo.stream_allocation.stream_id, dbo.dwh_validation.validation_check, dbo.foundtest.lookfor, dbo.foundtest.found, dbo.error_lookup.error_description, 
                         dbo.dwh_validation.validation_error,                         dbo.feed_exclution.exclution_id
FROM            dbo.feed_data LEFT OUTER JOIN
                         dbo.dwh_data AS PRO ON dbo.feed_data.material_id = PRO.material_id LEFT OUTER JOIN
                         dbo.dwh_data AS SDO ON dbo.feed_data.material_id = SDO.material_id LEFT OUTER JOIN
                         dbo.dwh_data AS ORG ON dbo.feed_data.material_id = ORG.material_id LEFT OUTER JOIN
                         dbo.dwh_data AS PTY ON dbo.feed_data.material_id = PTY.material_id LEFT OUTER JOIN
                         dbo.dwh_data AS PNO ON dbo.feed_data.material_id = PNO.material_id LEFT OUTER JOIN
                         dbo.dwh_data AS DSC ON dbo.feed_data.material_id = DSC.material_id LEFT OUTER JOIN
                         dbo.dwh_data AS SUP ON dbo.feed_data.material_id = SUP.material_id LEFT OUTER JOIN
                         dbo.dwh_data AS PAG ON dbo.feed_data.material_id = PAG.material_id LEFT OUTER JOIN
                         dbo.dwh_data AS TIT ON dbo.feed_data.material_id = TIT.material_id LEFT OUTER JOIN
                         dbo.dwh_data AS PFP ON dbo.feed_data.material_id = PFP.material_id LEFT OUTER JOIN
                         dbo.stream_allocation ON dbo.feed_data.xcp_id = dbo.stream_allocation.xcp_id LEFT OUTER JOIN
                         dbo.error_lookup ON dbo.stream_allocation.stream_id = dbo.error_lookup.error_id LEFT OUTER JOIN
                         dbo.foundtest ON dbo.foundtest.xcpid = dbo.feed_data.xcp_id LEFT OUTER JOIN
                         dbo.feeds ON dbo.feeds.feed_id = dbo.feed_data.feed_id INNER JOIN
                         dbo.dwh_validation ON dbo.feed_data.material_id = dbo.dwh_validation.upi LEFT OUTER JOIN
                         dbo.feed_exclution ON dbo.feed_data.feed_id = dbo.feed_exclution.feed_id AND dbo.feed_data.material_id = dbo.feed_exclution.upi OUTER apply
                             (SELECT        TOP 1 *
                               FROM            dbo.file_collation
                               WHERE        xcp_id = feed_data.xcp_id) collation
WHERE        (PRO.datatype = 'projectStatus') AND (SDO.datatype = 'standardsBody') AND (ORG.datatype = 'originatingOrg') AND (PTY.datatype = 'projectType') AND 
                         (PNO.datatype = 'projectNumber') AND (DSC.datatype = 'materialDescription') AND (SUP.datatype = 'supersedes') AND (PAG.datatype = 'pageCount') AND 
                         (TIT.datatype = 'materialTitle') AND (PFP.datatype = 'projectForecastPubl') AND dbo.feed_exclution.exclution_id IS NULL 


GO
SET QUOTED_IDENTIFIER OFF 
GO
SET ANSI_NULLS OFF 
GO

GO
SET QUOTED_IDENTIFIER ON 
GO
SET ANSI_NULLS ON 
GO
ALTER VIEW [forAllocation]
AS
SELECT        xcp_id, material_id, feed_id, projectStatus, standardsBody, originatingOrg, projectType, projectNumber, materialDescription, supersedes, stream_id, 
                         validation_check
FROM            dbo.mainData
WHERE        (projectStatus = 'PUBL') AND (validation_check = 1) AND (stream_id LIKE '9_' OR
                         stream_id IS NULL) OR
                         (validation_check = 1) AND (stream_id LIKE '9_' OR
                         stream_id IS NULL) AND (feed_id IN
                             (SELECT        feed_id
                               FROM            dbo.FEEDS
                               WHERE        (include_all = 1)))


GO
SET QUOTED_IDENTIFIER OFF 
GO
SET ANSI_NULLS OFF 
GO

GO
SET QUOTED_IDENTIFIER ON 
GO
SET ANSI_NULLS ON 
GO
ALTER VIEW [ACT_MAPPING_VIEW]
AS
SELECT        TOP (100) PERCENT dbo.ACT_MAPPING_LINK.pipeline_id, dbo.ACT_MAPPING.act_in, dbo.ACT_MAPPING.status_in, dbo.ACT_MAPPING.act_out, 
                         dbo.ACT_MAPPING.status_out, dbo.ACT_MAPPING.assign, dbo.ACT_MAPPING.id, dbo.ACT_MAPPING.action_id
FROM            dbo.ACT_MAPPING_LINK LEFT OUTER JOIN
                         dbo.ACT_MAPPING ON dbo.ACT_MAPPING_LINK.mapping_set_id = dbo.ACT_MAPPING.set_id
ORDER BY dbo.ACT_MAPPING_LINK.pipeline_id


GO
SET QUOTED_IDENTIFIER OFF 
GO
SET ANSI_NULLS OFF 
GO

GO
DROP View [dbo].[mainAudit]
GO
DROP View [dbo].[invalidData_PUBL]
GO
DROP View [dbo].[TEST]
GO
