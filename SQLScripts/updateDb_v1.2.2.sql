ALTER TABLE [dbo].[USERS] ADD [email] [varchar](200) NULL 
ALTER TABLE [dbo].[USERS] ADD [lastLogin] [datetime] NULL   
GO
CREATE TABLE [dbo].[ROLES] (
   [ID] [int] NOT NULL  
      IDENTITY (1,1) ,
   [role_name] [varchar](100) NOT NULL ,
   [role_description] [varchar](500) NOT NULL 

   ,CONSTRAINT [PK_ROLES] PRIMARY KEY CLUSTERED ([ID])
)

CREATE TABLE [dbo].[ROLE_USER_MAPPING] (
   [id] [int] NOT NULL  
      IDENTITY (1,1) ,
   [role_id] [int] NOT NULL   ,
   [user_id] [int] NOT NULL   

   ,CONSTRAINT [PK_ROLE_USER_MAPPING] PRIMARY KEY CLUSTERED ([id])
)

CREATE TABLE [dbo].[ACT_ROLE_MAPPING] (
   [ID] [int] NOT NULL  
      IDENTITY (1,1) ,
   [ACT_ID] [nchar](2) NOT NULL ,
   [ROLE_ID] [int] NOT NULL   

   ,CONSTRAINT [PK_ACT_ROLE_MAPPING] PRIMARY KEY CLUSTERED ([ID])
)

CREATE TABLE [dbo].[ACT_AUDIT_2] (
   [id] [int] NOT NULL  
      IDENTITY (1,1) ,
   [XCPID] [nchar](10) NOT NULL ,
   [activity] [nchar](2) NOT NULL ,
   [status] [nchar](2) NOT NULL ,
   [startedOn] [datetime] NOT NULL   ,
   [startedBy] [int] NOT NULL   ,
   [info] [varchar](max) NULL ,
   [allocatedTo] [int] NULL   ,
   [allocatedOn] [datetime] NULL   ,
   [allocatedBy] [int] NULL   ,
   [endedBy] [int] NULL   ,
   [endedOn] [datetime] NULL   ,
   [sentToId] [int] NULL   ,
   [sentToActivity] [nchar](2) NULL ,
   [sentToStatus] [nchar](2) NULL 

   ,CONSTRAINT [PK_ACT_AUDIT_2] PRIMARY KEY CLUSTERED ([id])
)

GO
SET QUOTED_IDENTIFIER ON 
GO
SET ANSI_NULLS ON 
GO
CREATE VIEW dbo.USER_ACTIVITY
AS
SELECT        dbo.USERS.id, dbo.ACT_ROLE_MAPPING.ACT_ID
FROM            dbo.USERS LEFT OUTER JOIN
                         dbo.ROLE_USER_MAPPING ON dbo.ROLE_USER_MAPPING.user_id = dbo.USERS.id LEFT OUTER JOIN
                         dbo.ACT_ROLE_MAPPING ON dbo.ACT_ROLE_MAPPING.ROLE_ID = dbo.ROLE_USER_MAPPING.role_id
WHERE        (dbo.ACT_ROLE_MAPPING.ACT_ID IS NOT NULL)

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
ALTER VIEW dbo.allData
AS
SELECT        dbo.feed_data.xcp_id, STUFF
                             ((SELECT        ',' + cast(pipeline_id AS varchar(2))
                                 FROM            PIPELINE_MAPPING PM
                                 WHERE        PM.stream_id = STREAM_ALLOCATION.STREAM_ID FOR XML PATH(''), TYPE ).value('.', 'varchar(max)'), 1, 1, '') AS pipeline_ids, 
                         COLLATION.file_location, dbo.feed_data.material_id, dbo.feed_data.feed_id, dbo.feeds.feed_name, dbo.feed_data.date_added, PRO.datavalue AS projectStatus, 
                         PAG.datavalue AS pageCount, SDO.datavalue AS standardsBody, ORG.datavalue AS originatingOrg, PTY.datavalue AS projectType, TIT.datavalue AS materialTitle, 
                         PNO.datavalue AS projectNumber, DSC.datavalue AS materialDescription, PFP.datavalue AS projectForecastPubl, SUP.datavalue AS supersedes, 
                         dbo.stream_allocation.stream_id, dbo.dwh_validation.validation_check, dbo.foundtest.lookfor, dbo.foundtest.found, dbo.error_lookup.error_description, 
                         dbo.dwh_validation.validation_error, CASE WHEN dbo.feed_exclution.exclution_id IS NULL THEN 1 ELSE 0 END AS INCLUDED, dbo.feed_exclution.exclution_id, 
                         stage.ACT + ':' + stage.STATUS stage, stage.username
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
                               WHERE        xcp_id = feed_data.xcp_id) collation OUTER apply
                             (SELECT        TOP 1 [ACT], [STATUS], username
                               FROM            dbo.ACT_AUDIT LEFT OUTER JOIN
                                                         dbo.USERS ON USERS.id = allocatedTo
                               WHERE        xcpid = feed_data.xcp_id
                               ORDER BY DATE DESC) stage
WHERE        (PRO.datatype = 'projectStatus') AND (SDO.datatype = 'standardsBody') AND (ORG.datatype = 'originatingOrg') AND (PTY.datatype = 'projectType') AND 
                         (PNO.datatype = 'projectNumber') AND (DSC.datatype = 'materialDescription') AND (SUP.datatype = 'supersedes') AND (PAG.datatype = 'pageCount') AND 
                         (TIT.datatype = 'materialTitle') AND (PFP.datatype = 'projectForecastPubl') AND dbo.feed_exclution.exclution_id IS NULL

GO
SET QUOTED_IDENTIFIER OFF 
GO
SET ANSI_NULLS OFF 
GO

-- Updated Audit to use new table
SET IDENTITY_INSERT ACT_AUDIT_2 ON
GO
INSERT INTO ACT_AUDIT_2
([id],[XCPID],[activity],[status],[startedOn],[startedBy],[info],[allocatedTo],[allocatedOn],[allocatedBy],[endedBy],[endedOn],[sentToId],[sentToActivity],[sentToStatus])
SELECT TOP 1000 mAudit.[ID] id
      ,mAudit.[XCPID] 
      ,mAudit.[ACT] activity
      ,mAudit.[STATUS] status
      ,mAudit.[DATE] startedOn
      ,mAudit.[USER_ID] startedBy
      ,mAudit.[DATA] info
      ,mAudit.[allocatedTo]
      ,mAudit.[allocatedOn]
      ,mAudit.[allocatedBy]
    ,sAudit.USER_ID endedBy
    ,sAudit.DATE endedOn
    ,sAudit.ID sentToId
    ,sAudit.ACT sentToActivity
    ,sAudit.STATUS sentToStatus
  FROM [ACT_AUDIT] mAudit
  outer apply (SELECT TOP 1 * FROM [ACT_AUDIT] where XCPID = mAudit.XCPID AND ID > mAudit.ID order by ID asc) sAudit
  GO
  SET IDENTITY_INSERT ACT_AUDIT_2 OFF
  GO