# Makefile for building the kinship appstore tarball.
#
# IMPORTANT: signature.json must be generated against the staged release tree
# in $(sign_dir), never against a development checkout. Signing a checkout
# signs .git/, node_modules/ and vendor-bin/ (57k+ files) which are stripped
# from the tarball, leaving thousands of FILE_MISSING integrity errors.
#
# Usage:
#   make appstore      # full pipeline: build -> stage -> sign -> tar
#   make sign          # (re)sign an already staged tree
#
# Signing key/cert are expected under $(cert_dir) (krankerl convention):
#   ~/.nextcloud/certificates/kinship.key and kinship.crt

app_name=kinship
build_dir=$(CURDIR)/build
source_dir=$(build_dir)/source
sign_dir=$(build_dir)/sign
appstore_dir=$(build_dir)/artifacts
cert_dir=$(HOME)/.nextcloud/certificates
occ?=php ../../occ

all: appstore

clean:
	rm -rf $(build_dir)

# Stage the release tree: only the files that will ship.
$(sign_dir)/$(app_name):
	mkdir -p $(sign_dir)
	rsync -a \
		--exclude=".git/" \
		--exclude="node_modules/" \
		--exclude="vendor-bin/" \
		--exclude="tests/" \
		--exclude="screenshots/" \
		--exclude="build/" \
		--exclude="*.key" --exclude="*.crt" --exclude="keys/" \
		--exclude=".nextcloudignore" --exclude="Makefile" \
		../$(app_name)/ $(sign_dir)/$(app_name)/

# Production dependencies and built assets inside the staged tree.
build-deps: $(sign_dir)/$(app_name)
	cd $(sign_dir)/$(app_name) && composer install --no-dev --prefer-dist
	cd $(sign_dir)/$(app_name) && npm ci && npm run build

# Sign the staged tree: signature.json then covers exactly the shipped set.
sign: $(sign_dir)/$(app_name)
	$(occ) integrity:sign-app \
		--privateKey=$(cert_dir)/$(app_name).key \
		--certificate=$(cert_dir)/$(app_name).crt \
		--path=$(sign_dir)/$(app_name)

appstore: clean $(sign_dir)/$(app_name) build-deps sign
	mkdir -p $(appstore_dir)
	tar -czf $(appstore_dir)/$(app_name).tar.gz -C $(sign_dir) $(app_name)
	@echo "Built $(appstore_dir)/$(app_name).tar.gz"

.PHONY: all clean build-deps sign appstore
