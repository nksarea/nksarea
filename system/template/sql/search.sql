SELECT %{field}%
FROM `classes`
JOIN `users` ON users.class = classes.id
JOIN `lists` ON lists.class = classes.id
JOIN `lists` ON lists.owner = users.id
JOIN `projects` ON projects.list = lists.id
JOIN `projects` ON projects.owner = users.id
JOIN `files` ON files.list = lists.id
JOIN `files` ON files.owner = users.id