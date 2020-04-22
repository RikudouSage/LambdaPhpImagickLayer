ARG PHP_VERSION

FROM bref/build-php-$PHP_VERSION as build

ARG IMAGE_MAGICK_VERSION=6.9.11-7
ARG IMAGE_MAGICK_MAJOR_VERSION=6

# Prepare environment
ENV IMAGICK_BUILD_DIR=${BUILD_DIR}/imagick
RUN mkdir -p ${IMAGICK_BUILD_DIR}
RUN LD_LIBRARY_PATH= yum -y install libwebp-devel wget

# Compile libde265
WORKDIR ${IMAGICK_BUILD_DIR}
RUN wget https://github.com/strukturag/libde265/releases/download/v1.0.5/libde265-1.0.5.tar.gz -O libde265.tar.gz
RUN tar xzf libde265.tar.gz
WORKDIR ${IMAGICK_BUILD_DIR}/libde265-1.0.5
RUN ./configure --prefix ${INSTALL_DIR} --exec-prefix ${INSTALL_DIR}
RUN make -j $(nproc)
RUN make install

# Compile libheif
WORKDIR ${IMAGICK_BUILD_DIR}
RUN wget https://github.com/strukturag/libheif/releases/download/v1.6.2/libheif-1.6.2.tar.gz -O libheif.tar.gz
RUN tar xzf libheif.tar.gz
WORKDIR ${IMAGICK_BUILD_DIR}/libheif-1.6.2
RUN ./configure --prefix ${INSTALL_DIR} --exec-prefix ${INSTALL_DIR}
RUN make -j $(nproc)
RUN make install

# Compile the ImageMagick library
WORKDIR ${IMAGICK_BUILD_DIR}
RUN wget https://imagemagick.org/download/ImageMagick-${IMAGE_MAGICK_VERSION}.tar.gz -O ImageMagick.tar.gz
RUN tar xzf ImageMagick.tar.gz
WORKDIR ${IMAGICK_BUILD_DIR}/ImageMagick-${IMAGE_MAGICK_VERSION}
RUN ./configure --prefix ${INSTALL_DIR} --exec-prefix ${INSTALL_DIR} --with-webp --with-heic --disable-hdri --disable-static
RUN make -j $(nproc)
RUN make install

# Compile the php imagick extension
WORKDIR ${IMAGICK_BUILD_DIR}
RUN pecl download imagick
RUN tar xzf imagick-3.4.4.tgz
WORKDIR ${IMAGICK_BUILD_DIR}/imagick-3.4.4
RUN phpize
RUN ./configure --with-imagick=${INSTALL_DIR}
RUN make -j $(nproc)
RUN make install
RUN cp `php-config --extension-dir`/imagick.so /tmp/imagick.so

FROM lambci/lambda:provided

ARG PHP_EXTENSION_DIR
ARG IMAGE_MAGICK_VERSION=6.9.11-7
ARG IMAGE_MAGICK_MAJOR_VERSION=6

# The ImageMagick libraries needed by the extension
COPY --from=build /opt/bref/lib/libMagickWand-${IMAGE_MAGICK_MAJOR_VERSION}.Q16.so.${IMAGE_MAGICK_MAJOR_VERSION}.0.0 /opt/bref/lib/libMagickWand-${IMAGE_MAGICK_MAJOR_VERSION}.Q16.so.${IMAGE_MAGICK_MAJOR_VERSION}
COPY --from=build /opt/bref/lib/libMagickCore-${IMAGE_MAGICK_MAJOR_VERSION}.Q16.so.${IMAGE_MAGICK_MAJOR_VERSION}.0.0 /opt/bref/lib/libMagickCore-${IMAGE_MAGICK_MAJOR_VERSION}.Q16.so.${IMAGE_MAGICK_MAJOR_VERSION}

# The ImageMagick dependencies
COPY --from=build /opt/bref/lib/libde265.so.0.0.12 /opt/bref/lib/libde265.so.0
COPY --from=build /opt/bref/lib/libheif.so.1.6.2 /opt/bref/lib/libheif.so.1
COPY --from=build /usr/lib64/libwebp.so.4.0.2 /opt/bref/lib/libwebp.so.4

# Move the imagick extension to the target directory
COPY --from=build /tmp/imagick.so /opt/bref/lib/php/extensions/no-debug-zts-${PHP_EXTENSION_DIR}/imagick.so
COPY imagick.ini /opt/bref/etc/php/conf.d/imagick.ini
