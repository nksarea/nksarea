SELECT p.id, p.owner, p.name, p.description, p.access_level, p.list, p.upload_time, users.class
FROM `projects` AS p
JOIN `users` ON p.owner = users.id
WHERE p.id = '%{pid}%';