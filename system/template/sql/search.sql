SELECT %{field}%
FROM `classes`
JOIN `users` ON users.class = classes.id
JOIN `lists` ON lists.class = classes.id ON lists.owner = users.id
JOIN `projects` ON projects.list = lists.id ON projects.owner = users.id
JOIN `files` ON files.list = lists.id ON files.owner = users.id