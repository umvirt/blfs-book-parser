# Umvirt BLFS book parser

Version: 0.1

License: GPL

Supported BLFS versions: 11.1 and later

## Preface

Linux from Scratch book (LFS) provides file with MD5 checksums.

Beyond Linux from Scratch (BLFS) book don't provide file with MD5 checksums. 
Manual processing or parsing is needed to generate such file.

## About

BLFS parser is utility which parse BLFS book source code and generate XML file with information about packages.

Informaton about BLFS packages can be used

* to compare BLFS versions
* to compare distributions with BLFS
* to speed up BLFS-based distributions update
* to download source package files 
* to check source package files availability
* to check source package files MD5 checksums

## Disclaimer

Data backup is needed before using this software.

All running applications should be closed.

** This software is can take all available memory which can hang a system, data loss and hardware damage!**

## How it works?

BLFS source code parsing is not simple task because it have Docbook format not pure XML.

### Fist stage 

On first stage a script "stage1" is perform:

* GIT-repository clonnig
* directories with files creation:
  * a text information with information about source package files is copied to .xml files with useless information.
  * information about commands are extracted to .cmd files

If GIT-repository directory exists cloning is skipped.
To force GIT-repository coning just delete it directory.

### Second stage

On second stage a script "stage2" is perform execution of script "parser.php" for each directories which was created on first stage.

A script "parser.php" is perform some tasks:

* extract links, filenames, MD5 checksums.
* encode BLFS source packages commands.
* perform single XML file creation.

## Preparation

Copy "config.sh.sample" to "config.sh" in directory "config" and edit "config.sh".

Copy "branches.txt.sample" to "branches.txt" in directory "config" and select BLFS GIT-repository branches to proccess in "branches.txt".

Create directories "tmp","out" manually or with command:

    make dirs

## Usage

Run ./stage1 to clone BLFS GIT-repositiry and generate directories with XML-files.

Run ./stage2 to generate XML-files.

