#-lgsoapck -lgsoap
GForge : Gforge.c  Gforge.h  SoapAPI.h
	g++-3.2 -ogforge  -DYYDEBUG -DDEBUG -g -I/home/mdupont/gsoap/gsoap-2.3/soapcpp2/ Gforge.c  -lgsoap++ -lgsoapck++ soapC.cpp  soapClient.cpp   -save-temps --verbose
#
#soapClientLib.cpp  
#soapServer.cpp 
#soapServerLib.cpp


SoapAPI.h :SoapAPI.wsdl
	export CLASSPATH=/home/mdupont/gsoap/gsoap-2.3/soapcpp2/wsdlcpp/:/home/mdupont/xerces/xerces-2_4_0/xmlParserAPIs.jar:/home/mdupont/xerces/xerces-2_4_0/xercesImpl.jar
	java    wsdlcpp file:SoapAPI.wsdl > SoapAPI.wsdl.tracee
	mv file:SoapAPI.h SoapAPI.h
	mv file:SoapAPI.c SoapAPI.c
	soapcpp2 SoapAPI.h

