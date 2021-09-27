PREFIX="localhost/fs/"

all: install

install:
	mkdir -p ./bin
	cp -f ./fs/bin/fs ./bin/fs
	[ $$(podman images --quiet ${PREFIX}fs/fs | wc -l) -gt 0 ] || podman build -t ${PREFIX}fs/fs -f ./fs/fs/Containerfile ./fs/sources
	echo $${PATH} | grep -q $${PWD}/bin || grep -Eq "^FS_PATH=$${PWD}/bin" ~/.$${SHELL##*/}rc || (echo "FS_PATH=$${PWD}/bin:\$$PATH && export PATH=\$$FS_PATH" >> ~/.$${SHELL##*/}rc && echo -e "\033[0;33mYou need to restart your bash to use the new PATH.\033[0m")

uninstall:
	rm -rf ./bin
	[ $$(podman images --quiet ${PREFIX} | wc -l) -eq 0 ] || podman rmi -f $$(podman images --quiet ${PREFIX})
	sed -i "/^FS_PATH=$${PWD//\//\\/}\/bin/d" ~/.$${SHELL##*/}rc

update: install
	podman build --no-cache --pull-always -t ${PREFIX}fs/fs -f ./fs/fs/Containerfile ./fs/sources

.PHONY: all install uninstall update
