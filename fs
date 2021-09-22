#!/bin/sh

fs_usage() {
  if [[ "$1" == "disable" ]]; then
    echo "Description:"
    echo "  Disable domains."
    echo "Usage:"
    echo "  fs disable [domains]"
    echo ""
    echo "Exemples:"
    echo "  fs disable php"
    echo "  fs disable node php"
    echo "  fs disable all"
  elif [[ "$1" == "enable" ]]; then
    echo "Description:"
    echo "  Enable domains."
    echo "Usage:"
    echo "  fs enable [domains]"
    echo ""
    echo "Exemples:"
    echo "  fs enable php"
    echo "  fs enable node php"
    echo "  fs enable all"
  else
    echo "Description:"
    echo "  Manage fictional system images and commands."
    echo "Usage:"
    echo "  fs [command]"
    echo ""
    echo "Available commands:"
    echo "  disable Disable domains"
    echo "  enable  Enable domains"
  fi
}

fs_error() {
  echo "Error: unrecognized command \`fs $*\`"
}

fs_disable() {
  if [ -f ./$1/.disabled ]
  then
    echo "${1} is already disable."
  else
    touch ./$1/.disabled
    echo "${1} disabled."
  fi
}

fs_enable() {
  if [ -f ./$1/.disabled ]
  then
    rm -f ./$1/.disabled
    echo "${1} enabled."
  else
    echo "${1} is already enable."
  fi
}

fs_change_status() {
  if [ $# -gt 1 ]
  then
    for domain in ${@:2}
    do
      if [ -d "$domain" ]
      then
        if [ "$domain" == "bin" ]
        then
          >&2 echo "${domain} is not a valid domain."
        else
            $1 "$domain"
        fi
      elif [ "$domain" == "all" ]
      then
        for directory in *
        do
          if [ -d "$directory" ] && [ "$directory" != "bin" ]
          then
            $1 "$directory"
          fi
        done
      else
        >&2 echo "${domain} is not a valid domain."
      fi
    done
  else
    fs_usage disable
  fi
}

if [ $# -eq 0 ]
then
  fs_usage
else
  if [[ "$1" == "disable" ]]; then
    fs_change_status fs_disable ${@:2}
  elif [[ "$1" == "enable" ]]; then
    fs_change_status fs_enable ${@:2}
  else
    fs_error $*
    fs_usage
  fi
fi
