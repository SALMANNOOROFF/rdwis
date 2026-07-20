-- Query: aud_revdata_add
-- Type: 64

INSERT INTO aud_revdata ( rvd_rev_id, rvd_attrib, rvd_alias, rvd_type, rvd_datatype, rvd_table, rvd_colname, rvd_conversion, rvd_rowid, rvd_oldvalue, rvd_newvalue )
SELECT [RevId] AS Rev, [Attrib] AS Arr1, [Alias] AS Arr2, [Type] AS Arr3, [DataType] AS Arr4, [TableName] AS Arr19, [ColName] AS Arr20, [Conversion] AS Arr23, [RowId] AS Row, [OldValue] AS OldVal, [NewValue] AS NewVal;

