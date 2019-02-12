#/bin/bash

cd /mnt/c/xampp/htdocs/Yelly/src;
php Yelly.php
rsync -av /mnt/c/xampp/htdocs/Yelly/output/ /mnt/c/Users/rocky/Documents/Personal/Blog/rockysynergy.github.io/
cd /mnt/c/Users/rocky/Documents/Personal/Blog/rockysynergy.github.io/
DATE=`date '+%Y-%m-%d %H:%M:%S'`
echo `pwd`
git add .
git commit -m"updated blog on {$DATE}"
git push origin master
