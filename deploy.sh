#!/bin/bash
# Unpack secrets; -C ensures they unpack *in* the .travis directory
tar xvf .travis/secrets.tar -C .travis

# Setup SSH agent:
eval "$(ssh-agent -s)" #start the ssh agent
chmod 600 .travis/build-key.pem
ssh-add .travis/build-key.pem

# Setup git defaults:
git config --global user.email "gon.gong.gone@gmail.com"
git config --global user.name "gong023"

# Add SSH-based remote to GitHub repo:
git remote add deploy git@github.com:gong023/namae-space.git
git fetch deploy

# Get box and build PHAR
wget https://box-project.github.io/box2/manifest.json
BOX_URL=$(php bin/parse-manifest.php manifest.json)
rm manifest.json
wget -O box.phar ${BOX_URL}
chmod 755 box.phar
./box.phar build -vv
# Without the following step, we cannot checkout the gh-pages branch due to
# file conflicts:
mv namaespace.phar namaespace.phar.tmp

# Checkout gh-pages and add PHAR file and version:
git checkout -b gh-pages deploy/gh-pages
mv namaespace.phar.tmp namaespace.phar
sha1sum namaespace.phar > namaespace.phar.version
git add namaespace.phar namaespace.phar.version

# Commit and push:
git commit -m 'Rebuilt phar'
git push deploy gh-pages:gh-pages
