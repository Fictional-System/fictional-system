DIRS = $(filter-out ./bin/., $(wildcard ./*/.))

all: prepare build install
force: prepare force_build install

prepare: $(DIRS:./%/.=prepare_%)
build: $(DIRS:./%/.=build_%)
force_build: $(DIRS:./%/.=force_build_%)
install: prepare_install $(DIRS:./%/.=install_%)
clean: $(DIRS:./%/.=clean_%)
	rm -rf ./bin
	sed -i '/^CC_PATH=/d' ~/.bashrc
force_clean: clean $(DIRS:./%/.=force_clean_%)
prepare_install:
	rm -rf ./bin
	mkdir -p ./bin
	echo $${PATH} | grep -q $${PWD}/bin || grep -Eq "^CC_PATH=" ~/.bashrc || (echo "CC_PATH=$${PWD}/bin:\$$PATH && export PATH=\$$CC_PATH" >> ~/.bashrc && echo -e "\033[0;33mYou need to restart your bash to use the new PATH.\033[0m")

prepare_%: %
	[ -f ./$</.disabled ] || if [ -d ./$</files ]; then mkdir -p ./$</local && cp -n ./$</files/* ./$</local/ || exit; fi

build_%: %
	[ -f ./$</.disabled ] || if [ -f ./$</local/versions ]; then while IFS= read -r VERSION; do podman build -t localhost/cc_$<:$${VERSION} -f ./$</Containerfile --build-arg VERSION=$${VERSION} ./$</local || exit; done < ./$</local/versions; fi

force_build_%: %
	[ -f ./$</.disabled ] || if [ -f ./$</local/versions ]; then while IFS= read -r VERSION; do podman build --pull-always -t localhost/cc_$<:$${VERSION} -f ./$</Containerfile --build-arg VERSION=$${VERSION} ./$</local || exit; done < ./$</local/versions; fi

install_%: %
	[ -f ./$</.disabled ] || if [ -f ./$</local/versions ]; then while IFS= read -r VERSION; do ./install $< $${VERSION} || exit; done < ./$</local/versions; fi

clean_%: %
	[ -f ./$</.disabled ] || [ $$(podman images --quiet localhost/cc_$< | wc -l) -eq 0 ] || podman rmi -f $$(podman images --quiet localhost/cc_$<)

force_clean_%: %
	[ -f ./$</.disabled ] || rm -rf ./$</local

enable_%: %
	[ -d ./$< ] && rm -f ./$</.disabled

disable_%: %
	[ -d ./$< ] && touch ./$</.disabled

.PHONY: all force prepare build force_build install clean force_clean prepare_install
