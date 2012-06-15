SELECT projects.name, projects.owner, projects.upload_time, projects.id, projects.color
FROM `projects`
JOIN `users` ON users.id = projects.owner
WHERE 1 AND NOT (((projects.access_level = 0) AND
				(projects.access_level = 1 AND %{accessLevel}% = 1) OR
				(projects.access_level <= 2 AND users.class = '%{class}%' AND %{accessLevel}% = 2) OR
				(projects.access_level <= 2 AND %{accessLevel}% = 4)) AND NOT projects.owner = %{id}% AND NOT %{accessLevel}% = 0)
ORDER BY projects.upload_time DESC
LIMIT 5