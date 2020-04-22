BUILDER = php builder/bin/app.php

layers: images create-builder
	${BUILDER} app:layers:create

images: create-builder
	${BUILDER} app:images:create

publish: layers create-builder
	${BUILDER} app:layers:publish

create-builder:
	cd builder && composer install && cd ..
