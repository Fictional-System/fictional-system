PREFIX=localhost/fs/

all: install

install: test
	mkdir -p ./bin
	cp -f ./fs/bin/fs ./bin/fs
	[ $$(podman images --quiet ${PREFIX}fs/fs | wc -l) -gt 0 ] || podman build -t ${PREFIX}fs/fs -f ./fs/fs/Containerfile ./fs/sources
	echo $${PATH} | grep -q $${PWD}/bin || grep -Eq "^FS_PATH=$${PWD}/bin" ~/.$${SHELL##*/}rc || (echo "FS_PATH=$${PWD}/bin:\$$PATH && export PATH=\$$FS_PATH" >> ~/.$${SHELL##*/}rc && echo -e "\033[0;33mYou need to restart your $${SHELL##*/} to use the new PATH.\033[0m")

uninstall:
	rm -rf ./bin
	[ $$(podman images --quiet ${PREFIX} | wc -l) -eq 0 ] || podman rmi -f $$(podman images --quiet ${PREFIX})
	sed -i "/^FS_PATH=$${PWD//\//\\/}\/bin/d" ~/.$${SHELL##*/}rc

update: install
	podman build -t ${PREFIX}fs/fs -f ./fs/fs/Containerfile ./fs/sources

test:
	podman build -t ${PREFIX}fs/test -f ./fs/fs/Containerfile ./fs/sources
	podman run --rm -it --userns=keep-id --name fs_test -v ${PWD}/fs/sources/test:/fs/test:z ${PREFIX}fs/test php /fs/test/test.php

test-dev:
	podman build -t ${PREFIX}fs/test-dev -f ${PWD}/fs/dev/Containerfile
	podman run --rm -it --userns=keep-id --name fs_test_dev -v ${PWD}/fs/sources:/fs:z ${PREFIX}fs/test-dev php /fs/test/test.php dev

.PHONY: all install uninstall update test test-dev
