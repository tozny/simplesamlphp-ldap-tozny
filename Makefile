all:
	@echo "Available targets:\n\tpackage -- Uses docker to package the module into a .deb package." 
   
package: docker_build 
	docker run -v $(PWD):/simplesamlphp-ldap-tozny/target tozny/simplesamlphp-ldap-tozny-package find . -name "simplesamlphp-ldap-tozny_*_all.deb" -exec cp {} target \;

docker_build: 
	docker build --no-cache -t 'tozny/simplesamlphp-ldap-tozny-package' .


