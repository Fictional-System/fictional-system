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
  echo >&2 "Error: unrecognized command \`fs $*\`"
}

fs_disable() {
  if [ -f ./$1/$2/.disabled ]
  then
    echo "${1}/${2} is already disabled."
  else
    touch ./$1/$2/.disabled && echo "${1}/${2} disabled." || >&2 echo "${1}/${2} cannot be disabled."
  fi
}

fs_enable() {
  if [ -f ./$1/$2/.disabled ]
  then
    rm -f ./$1/$2/.disabled && echo "${1}/${2} enabled." || >&2 echo "${1}/${2} cannot be enabled."
  else
    echo "${1}/${2} is already enabled."
  fi
}

fs_change_command_status() {
  if [ -d "$2/$3" ]
  then
    $1 $2 $3
  else
    echo >&2 "Error: \`${2}/${3}\` is not a valid component."
  fi
}

fs_change_component_status() {
  if [ $# -eq 2 ]
  then
    for directory in ${2}/*
    do
      if [ -d "$directory" ]
      then
        fs_change_command_status $1 $2 ${directory#"${2}/"}
      fi
    done
  elif [ $# -eq 3 ]
  then
    fs_change_command_status $1 $2 $3
  else
    fs_change_command_status $1 $2 $3 $4
  fi
}

fs_change_domain_status() {
  if [ $# -gt 1 ]
  then
    for value in ${@:2}
    do
      IFS="/"; read -ra parts <<< "${value}"; IFS=" ";
      local domain=${parts[0]}
      local component=${parts[1]}
      local command=${parts[2]}
      if [ -d "$domain" ]
      then
        if [ "$domain" == "bin" ]
        then
          >&2 echo "${domain} is not a valid domain."
        else
          if [ ${#parts[@]} -gt 1 ]
          then
            if [ ${#parts[@]} -gt 2 ]
            then
              fs_change_component_status "$1" "$domain" "$component" "$command"
            else
              fs_change_component_status "$1" "$domain" "$component"
            fi
          else
            fs_change_component_status "$1" "$domain"
          fi
        fi
      elif [ "$domain" == "all" ]
      then
        for directory in *
        do
          if [ -d "$directory" ] && [ "$directory" != "bin" ]
          then
            fs_change_component_status "$1" "$directory"
          fi
        done
      else
        >&2 echo "Error: \`${domain}\` is not a valid domain."
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
    fs_change_domain_status fs_disable ${@:2}
  elif [[ "$1" == "enable" ]]; then
    fs_change_domain_status fs_enable ${@:2}
  else
    fs_error $*
    fs_usage
  fi
fi
