BUILDER = php builder/bin/app.php

layers: images create-builder
	${BUILDER} app:layers:create

images: create-builder
	${BUILDER} app:images:create

publish: layers create-builder
	${BUILDER} app:layers:publish

clean:
	rm -rf export/*
	set -e; \
	for VERSION in $(VERSIONS); do \
	  	docker rmi rikudousage/layer-php-imagick-$${VERSION} || true; \
	done

create-builder:
	cd builder && composer install && cd ..
