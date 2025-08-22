#!/usr/bin/env bash

source "${MDC_BASEDIR}/bin/include/functions.sh"

# Check that the configuration file is present,
# stop if not to prevent accidental creation of unwanted containers.
if [ ! -f "mdc.env" ]; then
    mdc_echo_error "mdc.env file not found in current directory"
    exit 1
fi
if [ ! -f "${MDC_BASEDIR}/compose-webserver.yaml" ]; then
    mdc_echo_error "invalid MDC_BASEDIR=$MDC_BASEDIR"
    exit 1
fi

currentdir="$( pwd -P )";
export MDC_PROJECTDIR="$currentdir";

envbackup=$( export -p)
sharedenv="${HOME}/.mdc/mdc.env";
if [ -f "$sharedenv" ]; then
    echo "[mdc] Using ${sharedenv}"
    export $(grep -v '^#' $sharedenv | xargs)
fi
exportdata=$(grep -v '^#' mdc.env | xargs)
if [ ! -z "$exportdata" ]; then
    export $exportdata
fi
eval "$envbackup"
echo "[mdc] Using ${MDC_PROJECTDIR}/mdc.env"

if [ -z "$MDC_DIRROOT" ]; then
    # We know that mdc.env is in current directory, so use it as default value.
    export MDC_DIRROOT="${MDC_PROJECTDIR}";
fi

if [ -f "${MDC_DIRROOT}/public/lib/moodlelib.php" ]; then
    export MDC_PUBLIC_DIR=1;
else
    export MDC_PUBLIC_DIR=0;
fi

# Use current directory name as base for compose project name,
# users need to make sure it is unique enough.
# Unfortunately PhpStorm does not like any prefix in COMPOSE_PROJECT_NAME here.
name="$( basename "$( pwd -P )" )"
name=${name//_/-}
export COMPOSE_PROJECT_NAME="$name";

# Default values - keep them in sync with templates/mdc.env file.

if [ -z "${MDC_BACKUP_PATH}" ]; then
    export MDC_BACKUP_PATH="${HOME}/.mdc/backups"
fi

export MDC_PHP_VERSION="${MDC_PHP_VERSION:-8.1}"
export MDC_BEHAT_BROWSER="${MDC_BEHAT_BROWSER:-chromium}"
export MDC_BEHAT_BROWSER_VERSION="${MDC_BEHAT_BROWSER_VERSION:-4}"
export MDC_DB_TYPE="${MDC_DB_TYPE:-pgsql}"

if [ "$MDC_DB_TYPE" == "pgsql" ];then
    export MDC_DB_VERSION="${MDC_DB_VERSION:-16}"
elif [ "$MDC_DB_TYPE" == "mysqli" ]; then
    export MDC_DB_VERSION="${MDC_DB_VERSION:-8.4}"
    if [[ "${MDC_DB_VERSION}" == "5.7."* ]]; then
        export MDC_DB_COLLATION="${MDC_DB_COLLATION:-utf8_bin}"
    else
        # TODO: add support for utf8mb4_0900_as_cs collation
        export MDC_DB_COLLATION="${MDC_DB_COLLATION:-utf8mb4_bin}"
    fi
elif [ "$MDC_DB_TYPE" == "mariadb" ]; then
    export MDC_DB_VERSION="${MDC_DB_VERSION:-11.4}"
    # TODO: add support for uca1400_as_cs collations
    export MDC_DB_COLLATION="${MDC_DB_COLLATION:-utf8mb4_bin}"
elif [ "$MDC_DB_TYPE" == "sqlsrv" ]; then
    export MDC_DB_VERSION="${MDC_DB_VERSION:-latest}"
else
    mdc_echo_error "Unknown MDC_DB_TYPE=$MDC_DB_TYPE detected"
    exit 1
fi

# Extra site-install settings
export MDC_INSTALL_AGREE_LICENSE="${MDC_INSTALL_AGREE_LICENSE:-}"
export MDC_INSTALL_ADMINPASS="${MDC_INSTALL_ADMINPASS:-}"
