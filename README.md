# fictional-system

Podman containers as commands for development purpose.
This project allow you to build some images and use them as unix commmands.

## Requirements

- Podman
- Make

## Available make commands

- install: Build fs CAC and add bin dir to PATH.
- uninstall: Remove bin dir, image and bin dir to PATH.
- update: Pull last commit and make an installation.
- test: E2E tests.

## Creation/Modification

During build phase all files in **files** are copied in **cache** then **local** is also copied in **cache**.

### Containerfile
A simple Dockerfile

### commands.json

#### Overloadable options
Options are overload by tags options then by command options.

- interactive : true if the commands must be interactive.
- detached : true if the commmands must be detached.
- match_ids : true if the id must match between host and container.
- ports : list of all ports forwarding.
- volumes : list of all volumes binding.
- workdir : workdir for command.

#### Build options
Options are overload by tags options

- from
- arguments

### env
Env file can ben use in format : *command_for_host*.env
It will link automatically.

### Special configuration

#### Node - Angular, Ionic

Provide separate serve commands with the ports provided to be able to use the CLI while server is running.

NB : Ionic Lab cannot be used on a remote host until this PR is merged.
https://github.com/ionic-team/ionic-cli/pull/4650

#### Php - Xdebug -Composer

xdebug.env define env var for xdebug.
Composer cache is located in ~/.composer/cache.

#### Samba

- smb.conf : Config file for samba.
- users.list : List of users in user:password format (a default user present).

## Usage

After build you have a bunch of commands.
The format is *command*_*version*.
If *version* is *latest* the command is only *command*.
If more than one **command** has the same name the commands will be prefixed with component and domain.
