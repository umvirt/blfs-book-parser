# Index files

In addition to BLFS packages information stored in XML source some data stored in files list files or index files.

## Preface

In BLFS book almost all packages have one package file.
Optionaly additional files (add-ons) and patches can be provided.

There are exception. Some packages have many files. For example:

- xcb-utilities
- x7legacy
- x7lib
- x7app
- x7font
- frameworks6
- plasma-all

Information about files for this packages are not stored in XML sructure.

It's needed to extract information about all such files to download and store them.

Ignoring this files will broke other packages building which use it as dependencies.

We perform a small research which can be usefull for all Beyond Linux from Scratch users.

## How information about files is stored

First of all we need to understand how files are stored in chapters.

### Chapters

Chapters for installing packages with one file are contain instructions:

- how to prepare system (optional)
- how to configure source code
- how to build source code
- how to install source code 
- how to configure system (optional)

Chapters for installing packages with many files are looks same and contain additional instructions: 

- how to create files lists files 
- how to download files listed in this files

### Files lists files

Almost all files lists files are have same structure, they contain two fields:

- MD5 checksum
- filename 

There are one exception. Files list package x7legacy contain addition field directory:

- MD5 checksum
- directory
- filename

Additional field is used to compose download URLs.

## Extracting information

BLFS book is can be provided in HTML or PDF format.
This formats have one common source DocBook XML document.

Instead of parsing HTML or PDF we can parse XML document.

### Packages with multiple files list

To get packages with multiple files can search files which contain substring '.md5':

    find * -name '*.xml' -exec grep -l '.md5 ' {} +

In BLFS 13 We have to get:

    archive/x7proto.xml
    archive/pythonhosted.xml
    kde/plasma/plasma-all.xml
    kde/kf6/kf6-frameworks.xml
    postlfs/editors/gedit.xml
    x/installing/x7font.xml
    x/installing/x7lib.xml
    x/installing/xcb-utilities.xml
    x/installing/x7app.xml
    x/installing/x7legacy.xml

Some packages are archived. We can ignore them:

    find * -name '*.xml' -exec grep -l '.md5 ' {} + | grep -v archive

We have to get:

    kde/plasma/plasma-all.xml
    kde/kf6/kf6-frameworks.xml
    postlfs/editors/gedit.xml
    x/installing/x7font.xml
    x/installing/x7lib.xml
    x/installing/xcb-utilities.xml
    x/installing/x7app.xml
    x/installing/x7legacy.xml

This files are proper packages source code.

## Instructions parsing

Umvirt blfs-parser is store commands for each package xml file in cmd file. Without paths.

### Cmd files list

Create temporary directory 'tmp' and place cmd files in it.

To delete paths we can use a 'rev' and 'cut' commands:

    find * -name '*.xml' -exec grep -l '.md5 ' {} + | grep -v archive | rev| cut -d / -f 1 - | rev

We have to get:

    plasma-all.xml
    kf6-frameworks.xml
    gedit.xml
    x7font.xml
    x7lib.xml
    xcb-utilities.xml
    x7app.xml
    x7legacy.xml

Let's save this output in file to reduce pipe length in file in temporary directory:

    find * -name '*.xml' -exec grep -l '.md5 ' {} + | grep -v archive | rev| cut -d / -f 1 - | rev > tmp/pkgs.src

Open temporary directory

    cd tmp

We can open each cmd file to get wget list. All is needed to replace file extension from 'xml' to 'cmd'.

This can be done with command:

    cat pkgs.src | sed -e 's|.xml$|.cmd|'

We have to get:

    plasma-all.cmd
    kf6-frameworks.cmd
    gedit.cmd
    x7font.cmd
    x7lib.cmd
    xcb-utilities.cmd
    x7app.cmd
    x7legacy.cmd

File 'kf6-frameworks.cmd' is not exist, we need to use 'frameworks6.cmd'

    cat pkgs.src | rev| cut -d / -f 1 - | rev | sed -e 's|.xml$|.cmd|' | sed -e 's/kf6-frameworks.cmd/frameworks6.cmd/'

We have to get:

    plasma-all.cmd
    frameworks6.cmd
    gedit.cmd
    x7font.cmd
    x7lib.cmd
    xcb-utilities.cmd
    x7app.cmd
    x7legacy.cmd

### Parsing cmd files

It's possible to use Stream Editor to extract data from text files.

To get checksums for scpecific cmd file we can use command:

    cat x7lib.cmd | sed -n '/.md5 << "EOF"/,/EOF/p' | grep -v EOF

To get index file creation code use command:

    cat x7lib.cmd | sed -n '/.md5 << "EOF"/,/EOF/p'

To get wget call code use command:

    cat x7lib.cmd | sed -n '/EOF$/,/\.md5$/p' | grep -v EOF

To get checksums for all files use command:

    cat pkgs.src | sed -e 's|.xml$|.cmd|' | sed -e 's/kf6-frameworks.cmd/frameworks6.cmd/' | xargs cat | sed -n '/.md5 << "EOF"/,/EOF/p' | grep -v EOF | grep -v '^#'

To get index files creation code for all cmd files use command:

    cat pkgs.src | sed -e 's|.xml$|.cmd|' | sed -e 's/kf6-frameworks.cmd/frameworks6.cmd/' | xargs cat | sed -n '/.md5 << "EOF"/,/EOF/p'

To create index files just redirect pipe to bash:

    cat pkgs.src | sed -e 's|.xml$|.cmd|' | sed -e 's/kf6-frameworks.cmd/frameworks6.cmd/' | xargs cat | sed -n '/.md5 << "EOF"/,/EOF/p' | bash

!To create .md5 files for each index files use command:
!
!    for s in `ls *.md5` ; do
!        cat $s |  awk '{print $2}' > `echo $s | sed -e 's/.md5//'`.wget
!    done

To get wget urls use command:

    cat pkgs.src | sed -e 's|.xml$|.cmd|' | sed -e 's/kf6-frameworks.cmd/frameworks6.cmd/' | xargs cat | sed -n '/-B/,/$/p' | sed -e 's/-B/^/' | awk -F ' ' '{print $2}' | grep http

We have to get:

    https://www.x.org/pub/individual/font/
    https://www.x.org/pub/individual/lib/
    https://xorg.freedesktop.org/archive/individual/lib/
    https://www.x.org/pub/individual/app/
    https://www.x.org/pub/individual/

### Prearation for downloading

#### Checksum files

Create directory md5 to store checksums files

    mkdir md5

Create a file cmds2download which will contain a cmd files to download

    cat pkgs.src | sed -e 's|.xml$|.cmd|' | sed -e 's/kf6-frameworks.cmd/frameworks6.cmd/' | grep -v gedit > cmds2download

Run command to create .md5 files which same as cmd files names:

    for s in `cat cmds2download`; do
        w=`echo md5/$s| sed -e 's|.cmd$|.md5|'`
        if [ $s == 'x7legacy.cmd' ]
        then
            cat $s | sed -n '/.dat << "EOF"/,/EOF/p' | grep -v EOF | grep -v '^#' | awk '{print $1 "  " $3}'> $w
        else
            cat $s | sed -n '/.md5 << "EOF"/,/EOF/p' | grep -v EOF | grep -v '^#' > $w
        fi
    done

#### wget files

Create directory to store wget files:

    mkdir wget

Create .wget files:

    for s in `cat cmds2download`; do
        w=`echo wget/$s| sed -e 's|.cmd$|.wget|'`
        if [ $s == 'x7legacy.cmd' ]
        then
           cat $s | sed -n '/.dat << "EOF"/,/EOF/p' | grep -v EOF | grep -v '^#' | awk '{print $2 $3}'> $w
        else
          cat $s | sed -n '/.md5 << "EOF"/,/EOF/p' | grep -v EOF | grep -v '^#' | awk '{print $2}'> $w
        fi
    done

### Downloading

Go to wget directory

    cd wget

To get download script run:

    for s in `cat ../cmds2download`; do
        v1=`cat ../$s |  sed -n '/-B/,/$/p' | sed -e 's/-B/^/' | awk -F ' ' '{print $2}' | grep http`
        v2=`cat ../$s |  grep 'url=' | sed -e 's/url=//'`
        w=`echo $s| sed -e 's|.cmd$|.wget|'`
        d=`echo $s| sed -e 's|.cmd$||'`
        m=`echo ../md5/$s | sed -e 's|.cmd$|.md5|'`
        echo mkdir $d
        echo cd $d
        if [ $v1 ]
        then
            echo wget -i ../$w -B $v1 --no-check-certificate
        fi
        if [ $v2 ]
        then
            echo wget -i ../$w -B $v2 --no-check-certificate
        fi
        echo cd ..
    done

### Export

It's possible to generate  TSV (Tab-separated values) files to export information to packages database.

Fields

- md5-checksum
- filename
- download URL
- package

Make and go to tsv directory

    mkdir tsv
    cd tsv


Command to create packages files TSV files:

    for s in `cat ../cmds2download`; do
        v1=`cat ../$s |  sed -n '/-B/,/$/p' | sed -e 's/-B/^/' | awk -F ' ' '{print $2}' | grep http`
        v2=`cat ../$s |  grep 'url=' | sed -e 's/url=//'`
        #w=`echo $s| sed -e 's|.cmd$|.wget|'`
        p=`echo $s| sed -e 's|.cmd$||'`
        m=`echo ../md5/$s | sed -e 's|.cmd$|.md5|'`
        if [ $v1 ]
        then
            cat $m | sed -s 's/  /\t/' | sed -e "s|\$|\t$v1\t$p|" > $p.tsv
        fi
        if [ $v2 ]
        then
            cat $m | sed -s 's/  /\t/' |sed -e "s|\$|\t$v2\t$p|" > $p.tsv
        fi
    done

It's possible to combine packages files in one

    cat *.tsv > packages.lst

