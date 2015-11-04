SET NOCOUNT ON

IF EXISTS (SELECT * FROM   sys.objects WHERE  object_id = OBJECT_ID(N'[dbo].[Split]') AND type IN ( N'FN', N'IF', N'TF', N'FS', N'FT' ))
	BEGIN
		PRINT('Dropping existing Split function')
		DROP FUNCTION [dbo].[Split]		
	END
GO 

CREATE FUNCTION [dbo].[Split](@String varchar(8000), @Delimiter char(1))       
	returns @temptable TABLE (items varchar(8000))       
	as       
	begin       
		declare @idx int       
		declare @slice varchar(8000)       

		select @idx = 1       
			if len(@String)<1 or @String is null  return       

		while @idx!= 0       
		begin       
			set @idx = charindex(@Delimiter,@String)       
			if @idx!=0       
				set @slice = left(@String,@idx - 1)       
			else       
				set @slice = @String       

			if(len(@slice)>0)  
				insert into @temptable(Items) values(@slice)       

			set @String = right(@String,len(@String) - @idx)       
			if len(@String) = 0 break       
		end   
	return       
	end  
GO

PRINT('New Split function created')

DECLARE @tmp TABLE (Name VARCHAR(200))
DECLARE @exclude varchar(MAX)
DECLARE @excludeTable TABLE (Name VARCHAR(200))
DECLARE @table varchar(MAX)
DECLARE @col varchar(MAX)
DECLARE @colList varchar(MAX)
DECLARE @sourceTable varchar(200)
DECLARE @targetTable varchar(200)
DECLARE @sourceString varchar(200)
DECLARE @targetString varchar(200)
DECLARE @schema varchar(200)
DECLARE @count varchar(200)

SET @exclude = 'sysdiagrams,ACT_AUDIT_2,ACT_ROLE_MAPPING,ROLE_USER_MAPPING,ROLES,USERS'
SET @sourceTable = '[UAT-XCP]'
SET @targetTable = '[UAT-WSCRAPE]'
SET @schema = 'dbo'

INSERT INTO @excludeTable(Name) select * from dbo.split(@exclude,',')

INSERT INTO @tmp(Name) SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_TYPE = 'BASE TABLE'

WHILE (SELECT COUNT(*) FROM @tmp) > 0
BEGIN
	SET @table = (SELECT TOP 1 * FROM @tmp ORDER BY NAME asc)
	IF @table NOT IN (SELECT Name FROM @excludeTable)
		BEGIN
			PRINT('')
			PRINT( @table )
			PRINT( '------------' )
			SET @sourceString = @sourceTable+'.'+@schema+'.'+@table
			SET @targetString = @targetTable+'.'+@schema+'.'+@table
			IF EXISTS (SELECT * from syscolumns where id = Object_ID(@table) and colstat & 1 = 1)
				BEGIN
					PRINT( @table + ' has an identity column, use IDENTITY_INSERT')
					SET @colList = STUFF(( select ','+ COLUMN_NAME from information_schema.columns WHERE TABLE_NAME = @table FOR XML PATH('')),1,1,'')
					EXEC('	TRUNCATE TABLE '+@targetString+';
							SET IDENTITY_INSERT '+@targetString+' ON; 
							INSERT INTO '+@targetString+' ('+@colList+') SELECT '+@colList+' FROM '+@sourceString+';
							SET IDENTITY_INSERT '+@targetString+' OFF')
					PRINT 'DELETED: '+@targetString
					PRINT 'COPIED: '+@sourceString+' -> '+@targetString
				END
			ELSE
				BEGIN
					PRINT( @table + ' does not have an identity column')
					EXEC('	TRUNCATE TABLE '+@targetString+';
							INSERT INTO '+@targetString+' SELECT * FROM '+@sourceString)
					PRINT 'COPIED: '+@sourceString+' -> '+@targetString
				END
		END
	ELSE
		BEGIN
			PRINT 'EXCLUDED: '+@sourceString
		END
	DELETE FROM @tmp WHERE NAME = @table
	SET @sourceString = ''
	SET @targetString = ''
END

DROP FUNCTION [dbo].[Split]
PRINT('')
PRINT('New Split function dropped')
SET NOCOUNT OFF
