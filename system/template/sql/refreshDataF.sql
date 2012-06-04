SELECT files.name, files.owner, files.upload_time, files.list, files.mime, lists.class
FROM  `files`
JOIN lists ON files.list = lists.id
WHERE files.id = %{fid}%