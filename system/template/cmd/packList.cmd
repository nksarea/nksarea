mkdir projects
cd projects

%[createFolder,mkdir "$value",lol]%

%[packProject,cd $key
%|winRAR.cmd,e -inull "$value"|%
cd ..,
]%

cd ..