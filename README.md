# fictional-system

Podman containers as commands for development purpose.
This project allow you to build some images and use them as unix commmands.

## Prerequisite

- Podman
- Make

## Build

You have to build all images and commands by using Make. The build is execute only if the directory is activated.

- [all] : Do *prepare*, *build* and *install*.
- prepare : Prepare the files by copying **files** directory in **local**.
- build : Build the image with **local** as context.
- install : Recreate the **bin** directory and all scripts inside it. Also add new PATH in .bashrc.
- clean : Remove all images, **bin** directory and PATH from .bashrc.
- prepare_install : Is a pre-install rule (don't use it).

You also have forced rules.

- force : Do *prepare*, *force_build* and *install*.
- force_build : Same as *build* but force pull the images.
- force_clean : *clean* and remove **local** directory.

You also have targeted rules.

- prepare_**directory** : *prepare* for **directory** only.
- build_**directory** : *build* for **directory** only.
- install_**directory** : *install* for **directory** only.
- clean_**directory** : *clean* for **directory** only.
- force_build_**directory** : *force_build* for **directory** only.
- force_clean_**directory** : *force_clean* for **directory** only.
- enable_**directory** : Enable **directory**.
- disable_**directory** : Disable **directory**.

## Creation/Modification

During preparation phase all files in **files** are copied in **local**.
**local** is used as context for build.

### Containerfile
A simple Dockerfile

### options
Options in key=value format.

- interactive : true if the commands must be interactive.
- detached : true if the commmands must be detached.
- idmatch : true if the id must match between host and container.

### ports
List of port to publish.

### versions
List of versions to build.

### volumes
List of volumes to attach to the container.

### commands
List of commands in *command_for_host*=*command_in_container* format.
If this file is not present the command will have the name of the **directory** and will not take any arguments.

### env
List of environment variables included for commands.
Format : VARNAME=VALUE.
One variable per line.

### *command_for_host*_env
List of environment variables included for the command *command_for_host* only.

### Special configuration

#### Php - Composer

Composer cache is located in ~/.composer/cache.

#### Samba

- smb.conf : Config file for samba.
- users.list : List of users in user:password format (a default user present).

## Usage

After build you have a bunch of commands.
The format is *command*_*version*.
If **commands** file is present a *command* is available for each *command_for_host*.
If not *command* is the name of the **directory**.
