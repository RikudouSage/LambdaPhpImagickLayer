SHELL = /bin/bash
VERSIONS = 72 73 74

layers: images
	PWD=pwd
	rm -rf export/tmp
	rm -rf export/*.zip
	mkdir -p export/tmp
	set -e; \
	for VERSION in $(VERSIONS); do \
	  	cd ${PWD}; rm -rf export/tmp/*; cd export/tmp; \
		docker run --rm --entrypoint "tar" rikudousage/layer-php-imagick-$${VERSION} -ch -C /opt . | tar -x; \
		zip --quiet -X --recurse-paths ../`echo "layer-php-imagick-$${VERSION}"`.zip . ; \
	done
	rm -rf export/tmp

images:
	set -e; \
	for VERSION in $(VERSIONS); do \
	  	case $${VERSION} in \
	  		72) \
  		  		EXT_DIR=20170718 \
  		  		;; \
			73) \
				EXT_DIR=20180731 \
				;; \
			74) \
				EXT_DIR=20190902 \
				;; \
		esac; \
		docker build -t rikudousage/layer-php-imagick-$${VERSION} --build-arg PHP_VERSION=$${VERSION} --build-arg PHP_EXTENSION_DIR=$${EXT_DIR} . ; \
	done

publish: layers
	set -e; \
	for VERSION in $(VERSIONS); do \
		aws lambda publish-layer-version --layer-name imagick-$${VERSION} --zip-file fileb://./export/layer-php-imagick-$${VERSION}.zip; \
	done

clean:
	rm -rf export/*
	set -e; \
	for VERSION in $(VERSIONS); do \
	  	docker rmi rikudousage/layer-php-imagick-$${VERSION} || true; \
	done
