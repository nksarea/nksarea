SELECT projects.id, projects.name, projects.owner, projects.upload_time, projects.color
FROM `projects`
WHERE `list`='%{list}%'