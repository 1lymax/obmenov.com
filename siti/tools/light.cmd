@rem http://webmoney.ru/rus/developers/interfaces/xml/xml_php/index.shtml
openssl pkcs12 -in %1.pfx -out %1.key -nocerts
openssl pkcs12 -in %1.pfx -out %1.cer -clcerts -nokeys
