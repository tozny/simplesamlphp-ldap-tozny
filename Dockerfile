FROM ubuntu:14.04

MAINTAINER kirk@tozny.com
ENV DEBIAN_FRONTEND noninteractive

RUN apt-get -y update
RUN apt-get -y install build-essential
RUN mkdir /simplesamlphp-ldap-tozny

ADD ./LICENSE        /simplesamlphp-ldap-tozny/LICENSE
ADD ./default-enable /simplesamlphp-ldap-tozny/default-enable
ADD ./docs           /simplesamlphp-ldap-tozny/docs
ADD ./lib            /simplesamlphp-ldap-tozny/lib
ADD ./www            /simplesamlphp-ldap-tozny/www
ADD ./packaging      /simplesamlphp-ldap-tozny/packaging

WORKDIR /simplesamlphp-ldap-tozny

RUN ./packaging/debian/create_package.sh
 
