# DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN



<!-- PAGE 1 -->

 
  



<!-- PAGE 2 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
 
OPEN BANKING - DOMICILIACIÓN 
 
1. Introducción 
La domiciliación de pagos es un servicio que consiste en el débito automático de la 
cuenta de un cliente de una Entidad de Intermediación Financiera (EIF) mediante una 
ACH, para el pago de un servicio o un pago recurrente (Luz, agua, teléfono, internet, 
universidad, otros). 
A continuación, se detalla todos los servicios: 
- Generar Token 
http://test.bnb.com.bo/ClientAuthentication.API/api/v1/auth/token 
 
- Generar QR domiciliación con monto fijo 
http://test.bnb.com.bo/DirectDebit/api/Services/GetQRFixedAmount          
 
- Generar QR domiciliación con monto variable 
http://test.bnb.com.bo/DirectDebit/api/Services/GetQRVariableAmount 
 
- Obtiene la lista de las domiciliaciones generadas por estado 
http://test.bnb.com.bo/DirectDebit/api/Services/DirectDebitsReport 
 
- Obtiene la lista de las transacciones generadas por estado 
http://test.bnb.com.bo/DirectDebit/api/Services/DirectDebitsCollections 
 
- Realiza la actualización de los datos de un cliente y en caso de no existir, lo crea. 
http://test.bnb.com.bo/DirectDebit/api/Services/UpdateRecord 
 
- Obtiene una lista de los clientes registrados 
http://test.bnb.com.bo/DirectDebit/api/Services/ReportClient 
 
- Obtiene el detalle de una domiciliación a partir del Id QR 
http://test.bnb.com.bo/DirectDebit/api/Services/GetDetail 
 
- Obtiene la imagen QR de una domiciliación a partir del Id QR 
http://test.bnb.com.bo/DirectDebit/api/Services/GetQRImage 
 
- Actualiza las cuotas dentro del plan de pagos. 
Opcionalmente cancela la domiciliación. 
http://test.bnb.com.bo/DirectDebit/api/Services/UpdatePendingQuota 
 
 
 


<!-- PAGE 3 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
2. Visión General 
El ser vicio de generación de token debe ser invocado antes de cualquier consulta, 
debido a que el mismo debe enviarse en la cabecera de todas las solicitudes. 
 
3. Autenticación.  
La forma de autenticación para consumir los servicios es a través de un token (Bearer 
Token) que debe ser enviada en la cabecera de cada uno de los servicios a consumirse. 
4. Diseño 
4.1. Consumir Servicio 
 
5. POST: Token 
Este servicio genera un token de seguridad que se utilizará para hacer todas las 
solicitudes posteriores. El mismo recibe como parámetros de entrada el “accountId” y 
el “authorizationId” otorgados por el Banco. 
5.1. Ruta de acceso 
 
http://test.bnb.com.bo/ClientAuthentication.API/api/v1/auth/token 
 
5.2 Definiciones 
 
5.2.1. Entrada 
 
Property DataType Description 
accountId string Usuario otorgado por el BNB. 
authorizationId string Contraseña otorgado por el BNB. 



<!-- PAGE 4 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
 
5.2.2. Salida 
 
Property DataType Description 
success bool Obtendrá como resultado correcto: true, 
caso contrario: false. 
message string Obtendrá el "token" como resulta do 
correcto. Caso contrario "mensaje de 
error". 
 
5.3. Ejecutar 
 
Headers 
 
Content-Type application/json 
 
Body 
{ 
"accountId":"s9CG8FE7Id75ef2jeX9bUA==", 
"authorizationId":"713K7PvTlACs1gdmv9jGgA==" 
} 
 
Response 200 OK 
{  
"success": true, 
"message": " eyJhbGciOiJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctb
W9yZSNobWFjLXNoYTI1NiIsInR5cCI6IkpXVCJ9.eyJSb2xlIjoiVE9LRU4iLCJJbnZvbH
ZlZFBhcnR5SWQiOiIyODgiLCJJbnZvbHZlZFBhcnR5SWRUeXBlIjoiMyIsIkNyZWRlbn
RpYWxTdGF0dXMiOiIxIiwiZXhwIjoxNjcyNDA3NjQyLCJpc3MiOiJibmIuY29tLmJvIn 0.7
0i-0y7h_Z2za8HO8ePEQt2mqL7YJmPOsxOcoGSG39s" 
} 
 
5.4. Code snippet - cURL Token 
 
curl --location --request POST 'http://test.bnb.com.bo/ClientAuthentication.API/api/v1/a
uth/token' \ 
--header 'Content-Type: application/json' \ 
--data '{ 
    "accountId": "s9CG8FE7Id75ef2jeX9bUA==\", 
    "authorizationId": "713K7PvTlACs1gdmv9jGgA==\" 
}' 
 
6. POST: GetQRFixedAmount 
 


<!-- PAGE 5 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
Genera un código QR de domiciliación con monto fijo: 
 
-  El código QR debe ser escaneado y aprobado por otra EIF para su efectividad. 
-  Debe generarse un código QR para cada domiciliación de un cliente. 
-  Adicionalmente se registra su respectivo plan de pagos para control posterior. 
 
 
6.1. Ruta de acceso 
  
http://test.bnb.com.bo/DirectDebit/api/Services/GetQRFixedAmount 
 
6.2. Definiciones 
 
6.2.1. Autorización 
 
Property DataType Description 
token Bearer Token Colocar el token que se obtuvo del servicio 
"Generar Token". 
 
6.2.2. Entrada 
 
Property DataType Description 
currencyCode short 
Moneda que se va a utilizar para la 
transacción: 
- 1: Bolivianos. 
- 2: Dólares. 
Determina qué  cuenta del cliente será 
utilizada para efectuar los abonos 
correspondientes. 
amount double Monto fijo debe ser mayor que cero “0”.  
Maneja el punto (.) con 2 decimales. 
reference string(60) Descripción del servicio a domiciliar. 
serviceCode string(20) Código de servicio o contrato que identifica 
el servicio a domiciliar. 
dueDate string(10) 
Fecha de vencimiento del QR de 
domiciliación, se debe especificar en formato 
(yyyy-MM-dd). Si este campo va en blanco, 
por defecto el QR de domiciliación tendrá 30 
días de validez máxima. 
installmentsQuantity short Especifica el número de cuotas en el que se 
debe dividir el monto a domiciliar. 
scheduledDate string(10) Fecha de programación para el cobro de la 
primera cuota del QR de domiciliación, se 


<!-- PAGE 6 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
debe especificar en formato (yyyy-MM-dd 
HH:mm). La programación para el resto de 
las cuotas  es calculada a partir de este 
parámetro. 
paymentFrequency short 
Frecuencia de cobro para la generación del 
plan de pagos: 
1: Diario 
2: Semanal 
3: Mensual 
4: Trimestral 
5: Semestral 
6: Anual 
clientIdentifier string Identificador único del cliente 
 
6.2.3. Salida 
 
Property DataType Description 
method string Tipo de registro de domiciliación: 
- “QR”. 
qrId string Código único asignado al QR de  
domiciliación. 
qrContent string Cadena en base 64 que contiene la imagen 
del QR Domiciliación. 
mimeType string Especificación del tipo de datos 
proporcionado en el base64: actualmente es 
“image/jpeg” 
installments  Cuotas disponibles en el plan de pagos. 
id int Identificador de la cuota. 
amount double Monto de la cuota. 
scheduledDate string(10) Fecha a realizar el cobro de la cuota. 
code string Código de respuesta. 
success bool Obtendrá como resultado correcto: true, caso 
contrario: false. 
message string Descripción detallada del código de 
respuesta proporcionado. 
 
6.3. Ejecutar 
 
Headers 
 
Como se mencionó anteriormente, siempre que un servicio sea invocado se deberá     
incluir el token en la cabecera de la solicitud: 
 
Authorization: Bearer  


<!-- PAGE 7 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
eyJhbGciOiJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNobW
FjLXNoYTI1NiIsInR5cCI6IkpXVCJ9.eyJSb2xlIjoiVE9LRU4iLCJJbnZvbHZlZFBhcnR5S
WQiOiIyODgiLCJJbnZvbHZlZFBhcnR5SWRUeXBlIjoiMyIsIkNyZWRlbnRpYWxTdGF0
dXMiOiIxIiwiZXhwIjoxNjcyNDA3NjQyLCJpc3MiOiJibmIuY29tLmJvIn0.70i-
0y7h_Z2za8HO8ePEQt2mqL7YJmPOsxOcoGSG39s 
cache-control:no-cache 
Content-Type application/json 
 
Body 
{ 
  "currencyCode":1, 
  "amount":1000, 
  "reference":"Pago de telefonía”, 
  "serviceCode":"KH2XI”, 
  "dueDate":"2023-07-23”, 
  "installmentsQuantity":1, 
  "scheduledDate":"2023-07-27 10:00”, 
  "paymentFrequency":3, 
"clientIdentifier": "jluna" 
 } 
 
 
Response 200 OK 
{ 
    "data": { 
        "method": "QR", 
        "qrId": "22123001001000000239", 
        "qrContent": "xvauUNKV/vlzT31o6obq/dPCWlMZxkz+3q6O6N5vhApQ1nJ3TZyU
gEt6hQMdOz6KLgK4IpsEkzi+2N6lz88JlTpdtUaHoyEEMiOcqSj3xl+t2NbMAn2     
 Kab2zbufyZPdgbNsJsbIuJweqaugAc3j9ktqH0ds6zEsBQDDtNxe6H7MDDGenG
EAd76ZP1yiHfEvvRoazYITjQOKZACS4H3JWlh898xxImIYll zN7maqsqtEFYQiVf
BIrnV89Q1sn0T7CShSbcLVpLelhWJupsjJjBsFeIOQ4zBuELROb4MTeySynJHrs
Xb40pU7T1nkL2y1SfnWy83Y1n2D3H7FKSrp+zg==|1048DC9A6683889441395
15F6CFF04FE", 
        "mimeType": “image/jpeg”, 
        "installments": [{ 
                  "id": 1, 
                  "amount": 1000.0, 
                  "scheduledDate": "2023-07-27 10:00" 
} 
      ] 
    }, 
    "code": "SUC000", 
    "success": true, 
    "message": "OK" 


<!-- PAGE 8 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
} 
 
6.4. Code snippet - cURL GetQRFixedAmount 
 
curl --location --request POST 
'http://test.bnb.com.bo/DirectDebit/api/Services/GetQRFixedAmount' \ 
--header 'Authorization: Bearer  
 
eyJhbGciOiJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNob
WFjLXNoYTI1NiIsInR5cCI6IkpXVCJ9.eyJSb2xlIjoiVE9LRU4iLCJJbnZvbHZlZFBhcnR
5SWQiOiIyODgiLCJJbnZvbHZlZFBhcnR5SWRUeXBlIjoiMyIsIkNyZWRlbnRpYWxTdG
F0dXMiOiIxIiwiZXhwIjoxNjg4MDUwOTAyLCJpc3MiOiJibmIuY29tLmJvIn0.i1zj-
Kwr7flT3ccvaEJt6nbMR1o8cXm5luBFDnW72Jo' \ 
--header 'Content-Type: application/json' \ 
--data '{ 
  "currencyCode": 1, 
  "amount": 1000, 
  "reference": "pago de telefonia", 
  "serviceCode": "KH2XI", 
  "dueDate": "2023-07-23", 
  "installmentsQuantity": 1, 
  "scheduledDate": "2023-07-27 10:00", 
   "paymentFrequency":3, 
  "clientIdentifier": "jluna" 
}' 
 
7. POST: GetQRVariableAmount 
 
Genera un código QR de domiciliación con monto variable: 
 
- El código QR debe ser escaneado y aprobado por otra EIF para su efectividad. 
- Una vez escaneado y confirmado el QR, la domiciliación está vigente hasta que el 
destinatario revoque la misma. 
- Debe generarse un código QR para cada domiciliación de un cliente. 
- Adicionalmente se registra su respectivo plan de pagos para control posterior. 
 
7.1. Ruta de acceso 
  
http://test.bnb.com.bo/DirectDebit/api/Services/GetQRVariableAmount 
 
7.2. Definiciones 
 
7.2.1. Autorización 
 


<!-- PAGE 9 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
Property DataType Description 
token Bearer Token Colocar el token que se obtuvo del servicio 
"Generar Token". 
 
7.2.2. Entrada 
 
Property DataType Description 
currencyCode short 
Moneda q ue se va a utilizar para la 
transacción: 
- 1: Bolivianos. 
- 2: Dólares. 
Determina que cuenta del cliente será 
utilizada para efectuar los abonos 
correspondientes. 
amount double 
Especifica el monto a cobrar, se puede 
especificar los siguientes valores: 
- Mayor que “0”, el monto determinado será 
cobrado en cada cuota hasta que el 
cliente desactive la domiciliación. 
- Igual a “0”, representa un monto variable, 
el monto a cobrar en cada cuota debe 
especificarse por parte de la empresa 
usando el método en el punto 14  de este 
manual. 
reference string(60) Descripción del servicio a domiciliar. 
serviceCode string(20) Código único de servicio o contrato que 
identifica el servicio a domiciliar. 
dueDate string(10) 
Fecha de vencimiento del QR de 
domiciliación, se debe especificar en formato 
(yyyy-MM-dd). Si este campo va en blanco, 
por defecto el QR de domiciliación tendrá 30 
días de validez máxima. 
scheduledDate string(10) 
Fecha de programación para el cobro de la 
primera cuota del QR de domiciliación, se 
debe especificar en formato ( yyyy-mm-dd 
HH:mm).  
paymentFrequency short 
Frecuencia de cobro para la generación del 
plan de pagos: 
1: Diario 
2: Semanal 
3: Mensual 
4: Trimestral 


<!-- PAGE 10 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
5: Semestral 
6: Anual 
clientIdentifier string Identificador único del cliente 
 
7.2.3. Salida 
 
Property DataType Description 
method string Tipo de registro de domiciliación: 
- “QR”. 
qrId string Código único asignado al QR de 
domiciliación. 
qrContent string Cadena en base 64 que contiene la imagen 
del QR de domiciliación. 
mimeType string Especificación del tipo de datos 
proporcionado en el base64: actualmente es 
“image/jpeg” 
installments  Cuotas del plan de pagos 
id int Identificador de la cuota. 
amount double 
El monto de la cuota, de acuerdo al monto 
especificado en la solicitud. En el caso de 
determinar “0” en la solicitud, la siguiente 
cuota queda pendiente hasta que se 
actualice la misma. 
scheduledDate string(10) Fecha a realizar el cobro de la cuota. 
code string Código de respuesta. 
success bool Obtendrá como resultado correcto: true, caso 
contrario: false. 
message string Descripción detallada del código de 
respuesta proporcionado. 
 
 
 
7.3. Ejecutar 
 
Headers  
 
Como se mencionó anteriormente, siempre que un servicio sea invocado se deberá 
incluir el token en la cabecera de la solicitud:  
  
Authorization: Bearer  
 
eyJhbGciOiJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNobW
FjLXNoYTI1NiIsInR5cCI6IkpXVCJ9.eyJSb2xlIjoiVE9LRU4iLCJJbnZvbHZlZFBhcnR5S
WQiOiIyODgiLCJJbnZvbHZlZFBhcnR5SWRUeXBlIjoiMyIsIkNyZWRlbnRpYWxTdGF0


<!-- PAGE 11 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
dXMiOiIxIiwiZXhwIjoxNjcyNDA3NjQyLCJpc3MiOiJibmIuY29tLmJvIn0.70i-
0y7h_Z2za8HO8ePEQt2mqL7YJmPOsxOcoGSG39s 
cache-control: no-cache  
 
Content-Type application/json 
 
Body 
{ 
"currencyCode":1, 
"amount":0, 
"reference":"Pago de luz", 
"serviceCode":"KH2XI", 
"dueDate":"2023-07-23", 
"scheduledDate":"2023-12-23 10:00", 
"paymentFrequency":3, 
"clientIdentifier": "jluna" 
} 
 
Response 200 OK  
{ 
    "data": { 
        "method": "QR", 
        "qrId": "22123001001000000240", 
        "qrContent": "yAhOWGMsqODR4YYBt8T4IpYnfR5n8d0cpcTfZuiqT1+vzz8kAzvT7
eCahxS5Pr670dN0tr5Knv/tiY0BcubiaSivZ+ulHn3LXXLMxyxREvS4v3rQzihgxFf0
J3+EYonAMDkoMfc/lZLPYHKHlALN9xfAzfM/8eqagdEPsQyb/d9xwTnIWswGiGG
AOnzMFT8dy0QyIimatznj1/wud1qyEIhnVuvwLa0v7uNQGF40+Q/zzjJHZKneEQa
8WftIMQJ7CLl9Jkx8EqwFuSxsDCmjqJmJ7b8m0VgKGYP5T55WRN2IZWjMNS7
p4dZRL/vc/a0hNViSu5g8mCN/gye8Ryzf3A==|1048DC9A668388944139515F6C
FF04FE", 
        "mimeType": “image/jpeg”, 
        "installments": [{ 
                  "id": 1, 
                  "amount": 0.0, 
                  "scheduledDate": "2023-07-27 10:00" 
}] 
    }, 
    "code": "SUC000", 
    "success": true, 
    "message": "OK" 
} 
 
7.4. Code snippet - cURL GetQRVariableAmount 
 


<!-- PAGE 12 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
curl --location --request POST 
'http://test.bnb.com.bo/DirectDebit/api/Services/GetQRVariableAmount' \ 
--header 'Authorization: Bearer 
eyJhbGciOiJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNobW
FjLXNoYTI1NiIsInR5cCI6IkpXVCJ9.eyJSb2xlIjoiVE9LRU4iLCJJbnZvbHZlZFBhcnR5S
WQiOiIyODgiLCJJbnZvbHZlZFBhcnR5SWRUeXBlIjoiMyIsIkNyZWRlbnRpYWxTdGF0
dXMiOiIxIiwiZXhwIjoxNjcyNDA3NjQyLCJpc3MiOiJibmIuY29tLmJvIn0.70i-
0y7h_Z2za8HO8ePEQt2mqL7YJmPOsxOcoGSG39s' \ 
--header 'Content-Type: application/json' \ 
--data '{ 
"currencyCode":1, 
"amount":0, 
"reference":"Pago de luz", 
"serviceCode":"KH2XI", 
"dueDate":"2023-07-23", 
"installmentsQuantity": 1, 
"scheduledDate":"2023-02-01 10:00", 
"paymentFrequency":3, 
"clientIdentifier": "jluna" 
}' 
 
 
8. POST: DirectDebitsReport 
 
Recupera una lista de las domiciliaciones generadas por estado. 
 
8.1. Ruta de acceso 
  
http://test.bnb.com.bo/DirectDebit/api/Services/DirectDebitsReport 
  
8.2. Definiciones 
 
8.2.1. Autorización 
 
Property DataType Description 
token Bearer Token Colocar el token que se obtuvo del servicio 
"Generar Token". 
 
8.2.2. Entrada 
 
Property DataType Description 
startDate string Fecha de inicio de la consulta. El formato 
que maneja es (yyyy-MM-dd). 


<!-- PAGE 13 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
closureDate string Fecha final de la consulta. El formato que 
maneja es (yyyy-MM-dd). 
scheduleStatus short 
Estado de la domiciliación: 
- 1: Activa. 
- 2: Vigente. 
- 3: Concluida. 
- 4: Cancelada por la empresa. 
- 5: Cancelada por el cliente. 
En caso de insertar “0” se muestran todos 
sus estados 
 
8.2.3 Salida 
 
Property Tipo de dato Descripción 
schedules  Domiciliaciones 
qrId string Código único asignado al QR de  
domiciliación. 
type short 
Tipo de domiciliación: 
- 1: De monto fijo. 
- 2: De monto variable. 
amount double Monto de la cuota. 
currencyCode short 
Moneda utilizada para la transacción: 
- 1: Bolivianos. 
- 2: Dólares. 
serviceCode string Código de servicio o contrato que identifica 
el servicio a domiciliar. 
reference string Descripción de la domiciliación. 
clientIdentifier string Identificador único del cliente 
created string Fecha de creación de la domiciliación. 
scheduledDate string Fecha a realizar el cobro de la cuota. 
scheduleStatus short 
Estado de la domiciliación: 
- 1: Activa. 
- 2: Vigente. 
- 3: Concluída. 
- 4: Cancelada por la empresa. 
- 5: Cancelada por el cliente. 
code string Código de respuesta. 
success bool Obtendrá como resultado correcto: true, caso 
contrario: false. 
message string Descripción detallada del código de 
respuesta proporcionado. 
 
8.3. Ejecutar 


<!-- PAGE 14 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
 
Headers  
 
Como se mencionó anteriormente, siempre que un servicio sea invocado se deberá     
incluir el token en la cabecera de la solicitud:  
 
Authorization: Bearer 
 
eyJhbGciOiJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNobW
FjLXNoYTI1NiIsInR5cCI6IkpXVCJ9.eyJSb2xlIjoiVE9LRU4iLCJJbnZvbHZlZFBhcnR5S
WQiOiIyODgiLCJJbnZvbHZlZFBhcnR5SWRUeXBlIjoiMyIsIkNyZWRlbnRpYWxTdGF0
dXMiOiIxIiwiZXhwIjoxNjcyNDA3NjQyLCJpc3MiOiJibmIuY29tLmJvIn0.70i -0y7h_Z2za8
HO8ePEQt2mqL7YJmPOsxOcoGSG39s  
cache-control: no-cache  
 
Content-Type application/json 

Body 
{ 
    "startDate":"2023-02-01", 
"closureDate":"2023-07-04", 
"scheduleStatus": 2 
} 
 
Response 200 OK 
{ 
    "data": { 
        "schedules": [ 
            { 
                "qrId": "2014911613131323", 
                "type": 2, 
"amount": 0.0, 
                "currencyCode": 1, 
                "serviceCode": "REG6", 
                "reference": "Pago de encomiendas", 
     "clientIdentifier": "2319429", 
                "created": "2023-03-22 16:48", 
                "scheduledDate": "2023-03-23 15:30", 
                "scheduleStatus": 2 
            } ] 
    }, 
    "code": "SUC000", 
    "success": true, 
    "message": "OK" 
} 


<!-- PAGE 15 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
 
8.4. Code snippet - cURL DirectDebitsReport 
 
curl --location --request POST 
'http://test.bnb.com.bo/DirectDebit/api/Services/DirectDebitsReport' \ 
--header 'Authorization: Bearer 
eyJhbGciOiJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNobW
FjLXNoYTI1NiIsInR5cCI6IkpXVCJ9.eyJSb2xlIjoiVE9LRU4iLCJJbnZvbHZlZFBhcnR5S
WQiOiIyODgiLCJJbnZvbHZlZFBhcnR5SWRUeXBlIjoiMyIsIkNyZWRlbnRpYWxTdGF0
dXMiOiIxIiwiZXhwIjoxNjcyNDA3NjQyLCJpc3MiOiJibmIuY29tLmJvIn0.70i-
0y7h_Z2za8HO8ePEQt2mqL7YJmPOsxOcoGSG39s' \ 
--header 'Content-Type: application/json' \ 
--data-raw '{ 
    "startDate": "2023-02-01", 
"closureDate": "2023-07-04", 
"scheduleStatus": 2 
}' 
 
9. POST: DirectDebitsCollections 
 
Recupera una lista de las transacciones generadas por estado. 
 
9.1. Ruta de acceso 
 
http://test.bnb.com.bo/DirectDebit/api/Services/DirectDebitsCollections 
 
9.2. Definiciones 
 
9.2.1. Autorización 
 
Property DataType Description 
token Bearer Token Colocar el token que se obtuvo del servicio 
"Generar Token". 
 
9.2.2. Entrada 
 
Property DataType Description 
startDate string Fecha de inicio de la consulta. El formato 
que maneja es (yyyy-MM-dd). 
closureDate string 
Fecha final de la consulta. El formato que 
maneja es (yyyy-MM-dd). Tiene como 
restricción la consulta de 30 días. 
installmentStatus short Estado de la transacción: 


<!-- PAGE 16 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
- 1: Pendiente. 
- 2: Cobrado. 
- 3: Cobrado con retraso. 
- 4: Atrasado. 
- 5: No realizado. 
 
9.2.3. Salida 
 
Property DataType Description 
transactions  Transacciones de la domiciliación. 
qrId string Código único asignado al QR de 
domiciliación. 
type short 
Tipo de domiciliación: 
- 1: De cuota fija. 
- 2: De cuota variable. 
installentId short Identificador de la cuota. 
amount double Monto de la cuota. 
currencyCode short 
Moneda utilizada para la transacción: 
- 1: Bolivianos. 
- 2: Dólares. 
scheduledDate string Fecha a realizar el cobro de la cuota. 
retryNumber short Contador de reintentos de cobro. 
retryDate string Ultima fecha de reintento registrada. 
paymentDate string Fecha del pago de la cuota. 
result string Descripción de la última transacción. 
installmentStatus short 
Estado de la transacción: 
- 1: Pendiente. 
- 2: Cobrado. 
- 3: Cobrado con retraso. 
- 4: Atrasado. 
- 5: No realizado. 
code string Código de respuesta. 
success bool Obtendrá como resultado correcto: true, 
caso contrario: false. 
message message Descripción detallada del código de 
respuesta proporcionado. 
 
9.3. Ejecutar 
 
Headers  
 
Como se mencionó anteriormente, siempre que un servicio sea invocado se deberá 
incluir el token en la cabecera de la solicitud: 


<!-- PAGE 17 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
 
Authorization: Bearer 
 
eyJhbGciOiJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNobW
FjLXNoYTI1NiIsInR5cCI6IkpXVCJ9.eyJSb2xlIjoiVE9LRU4iLCJJbnZvbHZlZFBhcnR5S
WQiOiIyODgiLCJJbnZvbHZlZFBhcnR5SWRUeXBlIjoiMyIsIkNyZWRlbnRpYWxTdGF0
dXMiOiIxIiwiZXhwIjoxNjcyNDA3NjQyLCJpc3MiOiJibmIuY29tLmJvIn0.70i -0y7h_Z2za8
HO8ePEQt2mqL7YJmPOsxOcoGSG39s 
cache-control: no-cache 
 
Content-Type application/json 

Body  
{ 
    "startDate": "2023-02-01", 
"closureDate": "2023-07-04", 
"installmentStatus": 5 
} 
 
Response 200 OK 
{ 
    "data": { 
    "transactions": [ 
        { 
             "qrId": "2014911613131323", 
             "type": 2, 
             "installmentId": 1, 
"amount": 0.0, 
             "currencyCode": 1, 
             "scheduledDate": "2023-03-23 15:00", 
             "retryNumber": 3, 
             "retryDate": "2022-12-23", 
"paymentDate": "", 
             "result": “Saldo insuficiente”, 
"installmentStatus": 5 
        } 
    ] 
}, 
    "code": "SUC000", 
    "success": true, 
    "message": "OK" 
} 

9.4. Code snippet - cURL DirectDebitsCollections 
 


<!-- PAGE 18 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
curl --location --request POST 
'http://test.bnb.com.bo/DirectDebit/api/Services/DirectDebitsCollections' \ 
--header 'Authorization: Bearer 
eyJhbGciOiJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNobW
FjLXNoYTI1NiIsInR5cCI6IkpXVCJ9.eyJSb2xlIjoiVE9LRU4iLCJJbnZvbHZlZFBhcnR5S
WQiOiIyODgiLCJJbnZvbHZlZFBhcnR5SWRUeXBlIjoiMyIsIkNyZWRlbnRpYWxTdGF0
dXMiOiIxIiwiZXhwIjoxNjcyNDA3NjQyLCJpc3MiOiJibmIuY29tLmJvIn0.70i-
0y7h_Z2za8HO8ePEQt2mqL7YJmPOsxOcoGSG39s' \ 
--header 'Content-Type: application/json' \ 
--data-raw '{ 
    "startDate": "2023-02-01", 
"closureDate": "2023-07-04", 
"installmentStatus": 5 
}' 
 
 
10. POST: UpdateRecord 
 
Realiza la actualización de los datos de un cliente y en caso de no existir, lo crea. 
 
10.1. Ruta de acceso 
 
http://test.bnb.com.bo/DirectDebit/api/Services/UpdateRecord 
 
10.2. Definiciones 
 
10.2.1. Autorización 
 
Property DataType Description 
token Bearer Token Colocar el token que se obtuvo del servicio 
"Generar Token". 
 
 
 
10.2.2. Entrada 
 
Property DataType Description 
clients  Datos de los clientes 
identifier string Código único asignado al cliente. 
name string Nombre del cliente. 
address string Dirección del cliente. 
email string Correo electrónico del cliente. 
phoneNumber string Número de teléfono del cliente. 


<!-- PAGE 19 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
status short 
Estado del cliente (en caso de crear un 
cliente nuevo, este es creado como usuario 
“activo” automáticamente): 
- 1: Activo. 
- 2: Inactivo. 
 
10.2.3. Salida 
 
Property DataType Description 
clients  Clientes actualizados/creados 
identifier string Código único asignado al cliente. 
updated short 
Al actualizar o registrar o btendrá como 
resultado correcto: true, caso contrario: 
false. 
errorMessage string 
Mensaje de error en caso de que la 
actualización de los datos de un cliente sea 
fallido. 
code string Código de respuesta. 
success bool Obtendrá como resultado correcto: true, 
caso contrario: false. 
message message Descripción detallada del código de 
respuesta proporcionado. 
 
10.3. Ejecutar 
 
Headers  
 
Como se mencionó anteriormente, siempre que un servicio sea invocado se deberá 
incluir el token en la cabecera de la solicitud: 
 
Authorization: Bearer 
 
eyJhbGciOiJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNobW
FjLXNoYTI1NiIsInR5cCI6IkpXVCJ9.eyJSb2xlIjoiVE9LRU4iLCJJbnZvbHZlZFBhcnR5S
WQiOiIyODgiLCJJbnZvbHZlZFBhcnR5SWRUeXBlIjoiMyIsIkNyZWRlbnRpYWxTdGF0
dXMiOiIxIiwiZXhwIjoxNjcyNDA3NjQyLCJpc3MiOiJibmIuY29tLmJvIn0.70i -0y7h_Z2za8
HO8ePEQt2mqL7YJmPOsxOcoGSG39s 
cache-control: no-cache 
 
Content-Type application/json 

Body  
{ 
      "identifier": "jluna", 


<!-- PAGE 20 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
      "name": "Juan Luna Perez", 
      "address": "Avenida Principal No. 555", 
      "email": "juan@email.com.bo", 
      "phoneNumber": "77777777", 
      "status": 1 
} 
 
Response 200 OK 
{ 
    "data": { 
     "clients": [ 
        { 
              "identifier": "jluna", 
              "updated": true, 
              "errorMessage": "" 
        } 
     ] 
}, 
    "code": "SUC000", 
    "success": true, 
    "message": "OK" 
} 

10.4. Code snippet - cURL DirectDebitsCollections 
 
curl --location --request POST 
'http://test.bnb.com.bo/DirectDebit/api/Services/UpdateRecord' \ 
--header 'Authorization: Bearer 
eyJhbGciOiJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNobW
FjLXNoYTI1NiIsInR5cCI6IkpXVCJ9.eyJSb2xlIjoiVE9LRU4iLCJJbnZvbHZlZFBhcnR5S
WQiOiIyODgiLCJJbnZvbHZlZFBhcnR5SWRUeXBlIjoiMyIsIkNyZWRlbnRpYWxTdGF0
dXMiOiIxIiwiZXhwIjoxNjcyNDA3NjQyLCJpc3MiOiJibmIuY29tLmJvIn0.70i-
0y7h_Z2za8HO8ePEQt2mqL7YJmPOsxOcoGSG39s' \ 
--header 'Content-Type: application/json' \ 
--data-raw '{ 
 
    "clients": [ 
    { 
      "identifier": "jluna", 
      "name": "Juan Luna Perez", 
      "address": "Avenida Principal No. 555", 
      "email": "juan@email.com.bo", 
      "phoneNumber": "77777777", 
      "status": 1 
    } 


<!-- PAGE 21 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
  ] 
}' 
 
11. POST: ReportClient 
 
Recupera una lista de los clientes registrados. 
 
11.1. Ruta de acceso 
 
http://test.bnb.com.bo/DirectDebit/api/Services/ReportClient 
 
11.2. Definiciones 
 
11.2.1. Autorización 
 
Property DataType Description 
token Bearer Token Colocar el token que se obtuvo del servicio 
"Generar Token". 
 
11.2.2. Entrada 
 
Property DataType Description 
startDate string Fecha de inicio de la consulta. El formato 
que maneja es (yyyy-MM-dd). 
closureDate string 
Fecha final de la consulta. El formato que 
maneja es (yyyy-MM-dd). Tiene como 
restricción la consulta de 30 días. 
clientStatus short 
Estado del cliente registrado: 
- 1: Activo. 
- 2: Inactivo. 
 
 
 
11.2.3. Salida 
 
Property DataType Description 
clients  Clientes registrados. 
identifier string Código único asignado al cliente. 
name string Nombre del cliente. 
address string Dirección del cliente. 
email string Correo electrónico del cliente. 
phoneNumber string Número de teléfono del cliente. 


<!-- PAGE 22 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
clientStatus short 
Estado del cliente: 
- 1: Activo. 
- 2: Inactivo. 
code string Código de respuesta. 
success bool Obtendrá como resultado correcto: true, 
caso contrario: false. 
message message Descripción detallada del código de 
respuesta proporcionado. 
 
11.3. Ejecutar 
 
Headers  
 
Como se mencionó anteriormente, siempre que un servicio sea invocado se deberá 
incluir el token en la cabecera de la solicitud: 
 
Authorization: Bearer 
 
eyJhbGciOiJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNobW
FjLXNoYTI1NiIsInR5cCI6IkpXVCJ9.eyJSb2xlIjoiVE9LRU4iLCJJbnZvbHZlZFBhcnR5 S
WQiOiIyODgiLCJJbnZvbHZlZFBhcnR5SWRUeXBlIjoiMyIsIkNyZWRlbnRpYWxTdGF0
dXMiOiIxIiwiZXhwIjoxNjcyNDA3NjQyLCJpc3MiOiJibmIuY29tLmJvIn0.70i -0y7h_Z2za8
HO8ePEQt2mqL7YJmPOsxOcoGSG39s 
cache-control: no-cache 
 
Content-Type application/json 

Body  
{ 
    "startDate": "2023-01-01", 
"closureDate": "2023-07-04", 
"clientStatus": 1 
 
} 
 
Response 200 OK 
{ 
        "data": { 
        "clients": [ 
            { 
                "identifier": "jluna", 
                "name": "Juan Luna Perez", 
                "address": "Avenida Principal No. 555", 
                "email": "juan@email.com.bo", 


<!-- PAGE 23 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
                "phoneNumber": "77777777", 
                "clientStatus": 1 
        } 
     ] 
}, 
    "code": "SUC000", 
    "success": true, 
    "message": "OK" 
} 
 
11.4. Code snippet - cURL ReportClient 
 
curl --location --request POST 
'http://test.bnb.com.bo/DirectDebit/api/Services/ReportClient' \ 
--header 'Authorization: Bearer 
eyJhbGciOiJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNobW
FjLXNoYTI1NiIsInR5cCI6IkpXVCJ9.eyJSb2xlIjoiVE9LRU4iLCJJbnZvbHZlZFBhcnR5S
WQiOiIyODgiLCJJbnZvbHZlZFBhcnR5SWRUeXBlIjoiMyIsIkNyZWRlbnRpYWxTdGF0
dXMiOiIxIiwiZXhwIjoxNjcyNDA3NjQyLCJpc3MiOiJibmIuY29tLmJvIn0.70i-
0y7h_Z2za8HO8ePEQt2mqL7YJmPOsxOcoGSG39s' \ 
--header 'Content-Type: application/json' \ 
--data-raw '{ 
    "startDate": "2023-01-01", 
"closureDate": "2023-07-04", 
"clientStatus": 1 
}' 
 
 
12. POST: GetDetail 
 
Obtiene el detalle de una domiciliación a partir del Id de QR. 
 
12.1. Ruta de acceso 
 
http://test.bnb.com.bo/DirectDebit/api/Services/GetDetail 
12.2. Definiciones 
 
12.2.1. Autorización 
 
Property DataType Description 
token Bearer Token Colocar el token que se obtuvo del servicio 
"Generar Token". 
 
12.2.2. Entrada 


<!-- PAGE 24 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
 
Property DataType Description 
qrId string Código único asignado al QR de 
domiciliación. 
 
12.2.3. Salida 
 
Property DataType Description 
currencyCode short 
Moneda que se va a utilizar para la 
transacción: 
- 1: Bolivianos. 
- 2: Dólares . 
Determina que cuenta del cliente será 
utilizada para efectuar los abonos 
correspondientes. 
amount double Monto del servici o o bien para la 
domiciliación. 
reference string(60) Descripción del servicio a domiciliar. 
serviceCode string(20) Código de servicio o contrato que identifica 
el servicio a domiciliar. 
type short 
Tipo de domiciliación: 
- 1: cuota fija. 
- 2: cuota variable. 
clientIdentifier string Identificador único del cliente 
scheduleStatus short 
Estado de la domiciliación: 
- 1: Activa. 
- 2: Vigente. 
- 3: Concluida. 
- 4: Cancelada por la empresa. 
- 5: Cancelada por el cliente. 
installments  Cuotas del plan de pagos 
id short Identificador de la cuota 
amount double Monto de la cuota. 
scheduledDate string 
Fecha de programación para el cobro, 
especificada en formato (yyyy -MM-dd 
HH:mm). 
retryNumber short Contador de reintentos de cobro. 
retryDate string Última fecha de reintento registrada. 
paymentDate String(10) 
Fecha de pago registrada en formato ( yyyy-
MM-dd). Obtiene vacío (“”) si el pago no ha 
sido efectuado. 


<!-- PAGE 25 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
result string 
Motivo de rechazo en caso de que el pago no 
haya sido efectuado. Vacío (“”) si el pago ha 
sido efectuado correctamente. 
status Short 
- 0: Cobro pendiente 
- 1: Cobro realizado 
- 2: Cobro rechazado 
code string Código de respuesta. 
success bool Obtendrá como resultado correcto: true, 
caso contrario: false. 
message string Descripción detallada del código de 
respuesta proporcionado. 
 
12.3. Ejecutar 
 
Headers  
 
Como se mencionó anteriormente, siempre que un servicio sea invocado se deberá 
incluir el token en la cabecera de la solicitud:  
 
Authorization: Bearer 
 
eyJhbGciOiJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNobWFjL
XNoYTI1NiIsInR5cCI6IkpXVCJ9.eyJSb2xlIjoiVE9LRU4iLCJJbnZvbHZlZFBhcnR5SWQiOiI
yODgiLCJJbnZvbHZlZFBhcnR5SWRUeXBlIjoiMyIsIkNyZWRlbnRpYWxTdGF0dXMiOiIxIiw
iZXhwIjoxNjcyNDA3NjQyLCJpc3MiOiJibmIuY29tLmJvIn0.70i -0y7h_Z2za8HO8ePEQt2mq
L7YJmPOsxOcoGSG39s 
cache-control: no-cache 
 
Content-Type application/json 
Body 
{ 
    "qrId":" 23070501001000001071 " 
} 
 
 Response 200 OK  
{ 
    "data": { 
    "currencyCode" : 1, 
    "amount" :  1000.00, 
    "reference" : "Pago de telefonía”, 
    "serviceCode" : "KH2XI” , 
    "type" : 1, 
"clientIdentifier": "jluna ", 
"scheduleStatus": 1, 
    "installments" : [ 


<!-- PAGE 26 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
{ 
                    "id": 1, 
                   "amount": 1000.00 
                              "scheduledDate": “2023-06-25 10:00”, 
                    "retryNumber": 0, 
   "retryDate": “”, 
                    "paymentDate": “”, 
                    "result": “” 
   "status": 0 
 
}] 
}, 
"code": "SUC000", 
    "success": true, 
"message": "OK" 
    } 
 
12.4. Code snippet - cURL GetDetail 
 
curl --location --request POST 
'http://test.bnb.com.bo/DirectDebit/api/Services/GetDetail' \ 
--header 'Authorization: Bearer 
eyJhbGciOiJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNobW
FjLXNoYTI1NiIsInR5cCI6IkpXVCJ9.eyJSb2xlIjoiVE9LRU4iLCJJbnZvbHZlZFBhcnR5S
WQiOiIyODgiLCJJbnZvbHZlZFBhcnR5SWRUeXBlIjoiMyIsIkNyZWRlbnRpYWxTdGF0
dXMiOiIxIiwiZXhwIjoxNjcyNDA3NjQyLCJpc3MiOiJibmIuY29tLmJvIn0.70i-
0y7h_Z2za8HO8ePEQt2mqL7YJmPOsxOcoGSG39s' \ 
--header 'Content-Type: application/json' \ 
--data-raw '{ 
    "qrId":" 23070501001000001071" 
}' 
 
13. POST: GetQRImage 
 
Obtiene la imagen QR generada previamente de una domiciliación a partir del Id de 
QR. 
 
13.1. Ruta de acceso 
 
http://test.bnb.com.bo/DirectDebit/api/Services/GetQRImage 
 
13.2. Definiciones 
 
13.2.1. Autorización 
 


<!-- PAGE 27 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
Property DataType Description 
token Bearer Token Colocar el token que se obtuvo del servicio 
"Generar Token". 
 
13.2.2. Entrada 
 
Property DataType Description 
qrId string Código único asignado al QR de 
domiciliación. 
 
13.2.3. Salida 
 
Property DataType Description 
method string Tipo de registro de domiciliación: 
- “QR”. 
qrContent string Cadena en base 64 que contiene la imagen 
del QR Domiciliación. 
mimeType string Especificación del tipo de datos 
proporcionado en el base64: actualmente es 
“image/jpeg” 
code string Código de respuesta. 
success bool Obtendrá como resultado correcto: true, 
caso contrario: false. 
message string Descripción detallada del código de 
respuesta proporcionado. 
 
 
13.3. Ejecutar 
 
Headers  
 
Como se mencionó anteriormente, siempre que un servicio sea invocado se deberá 
incluir el token en la cabecera de la solicitud:  
 
Authorization: Bearer 
 
eyJhbGciOiJodHRwOi8vd3d3LnczLm9yZ y8yMDAxLzA0L3htbGRzaWctbW9yZSNobWFjL
XNoYTI1NiIsInR5cCI6IkpXVCJ9.eyJSb2xlIjoiVE9LRU4iLCJJbnZvbHZlZFBhcnR5SWQiOiI
yODgiLCJJbnZvbHZlZFBhcnR5SWRUeXBlIjoiMyIsIkNyZWRlbnRpYWxTdGF0dXMiOiIxIiw
iZXhwIjoxNjcyNDA3NjQyLCJpc3MiOiJibmIuY29tLmJvIn0.70i -0y7h_Z2za8HO8ePEQt2mq
L7YJmPOsxOcoGSG39s 
cache-control: no-cache 
 


<!-- PAGE 28 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
Content-Type application/json 
Body 
{ 
    "qrId":"23070501001000001071" 
} 
 
Response 200 OK 
{ 
    "data": { 
        "method": "QR", 
        "qrContent": "xvauUNKV/vlzT31o6obq/dPCWlMZxkz+3q6O6N5vhApQ1nJ3TZyU
gEt6hQMdOz6KLgK4IpsEkzi+2N6lz88JlTpdtUaHoyEEMiOcqSj3xl+t2NbMAn2     
 …", 
        "mimeType": “image/jpeg” 
    }, 
    "code": "SUC000", 
    "success": true, 
    "message": "OK" 
} 
 
13.4. Code snippet - cURL GetQRImage 
 
curl --location --request POST 
'http://test.bnb.com.bo/DirectDebit/api/Services/GetQRImage' \ 
--header 'Authorization: Bearer 
eyJhbGciOiJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNobW
FjLXNoYTI1NiIsInR5cCI6IkpXVCJ9.eyJSb2xlIjoiVE9LRU4iLCJJbnZvbHZlZFBhcnR5S
WQiOiIyODgiLCJJbnZvbHZlZFBhcnR5SWRUeXBlIjoiMyIsIkNyZWRlbnRpYWxTdGF0
dXMiOiIxIiwiZXhwIjoxNjcyNDA3NjQyLCJpc3MiOiJibmIuY29tLmJvIn0.70i-
0y7h_Z2za8HO8ePEQt2mqL7YJmPOsxOcoGSG39s' \ 
--header 'Content-Type: application/json' \ 
--data-raw '{ 
    "qrId":" 23070501001000001071" 
}' 
 
 
14. POST: UpdatePendingQuota 
 
Actualiza las cuotas dentro del plan de pagos que se encuentren en estado pendiente. 
Adicionalmente se puede desactivar la domiciliación de acuerdo con el estado 
enviado. 
 
14.1. Ruta de acceso 
 
http://test.bnb.com.bo/DirectDebit/api/Services/UpdatePendingQuota 


<!-- PAGE 29 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
 
14.2. Definiciones 
 
14.2.1. Autorización 
 
Property DataType Description 
token Bearer Token Colocar el token que se obtuvo del servicio 
"Generar Token". 
 
14.2.2. Entrada 
 
Property DataType Description 
qrId string Código único asignado al QR de 
domiciliación. 
scheduleStatus short 
(Opcional) se utiliza este campo para 
desactivar/revocar la domiciliación enviando 
el siguiente valor: 
4: Cancelado por la empresa. 
installments  Cuotas disponibles en el plan de pagos. 
id short Identificador de la cuota. 
amount double 
Monto de la cuota, es validado de acuerdo al 
total de cuotas disponibles y el monto total de 
la domiciliación. 
scheduledDate string Fecha de cobro programada en formato 
(yyyy-MM-dd HH:mm). 

14.2.3. Salida 
 
Property DataType Description 
qrId string Código único asignado al QR de 
domiciliación. 
installments  Cuotas disponibles en el plan de pagos. 
id short Identificador de la cuota. 
status bool 
Estado de actualización del monto para el 
plan de pagos: 
- true: exitoso. 
- false: fallido. 
errorMessage string Mensaje de error en caso de que la 
actualización de una cuota sea fallido. 
code string Código de respuesta. 
success bool Obtendrá como resultado co rrecto: true, 
caso contrario: false. 


<!-- PAGE 30 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
message string Descripción detallada del código de 
respuesta proporcionado. 
 
14.3. Ejecutar 
 
Headers  
 
Como se mencionó anteriormente, siempre que un servicio sea invocado se deberá 
incluir el token en la cabecera de la solicitud:  
 
Authorization: Bearer 
 
eyJhbGciOiJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNobWFjL
XNoYTI1NiIsInR5cCI6IkpXVCJ9.eyJSb2xlIjoiVE9LRU4iLCJJbnZvbHZlZFBhcnR5SWQiOiI
yODgiLCJJbnZvbHZlZFBhcnR5SWRUeXBlIjoiMyIsIkNyZWRlbnRpYWxTdGF0dXMiOiIxIiw
iZXhwIjoxNjcyNDA3NjQyLCJpc3MiOiJibmIuY29tLmJvIn0.70i -0y7h_Z2za8HO8ePEQt2mq
L7YJmPOsxOcoGSG39s 
cache-control: no-cache 
 
Content-Type application/json 

Body 
{   
    "qrId": "23062301001000001030", 
    "scheduleStatus": 0, 
    "installments": [ 
                { 
                  "id":1, 
                  "amount":1000, 
                  "scheduledDate":"2023-06-23 15:30" 
                } 
] 
} 
 
 Response 200 OK  
{ 
    "data": { 
    "qrId": "23062301001000001030" 
           "installments": [ 
{ 
                    "id": 1, 
                    "status": true, 
                    "errorMessage": “”, 
}] 
}, 


<!-- PAGE 31 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
"code": "SUC000", 
    "success": true, 
 "message": "OK" 
} 
 
14.4. Code snippet - cURL UpdatePendingQuota 
 
curl --location --request POST 
'http://test.bnb.com.bo/DirectDebit/api/Services/UpdatePendingQuota' \ 
--header 'Authorization: Bearer 
eyJhbGciOiJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNobW
FjLXNoYTI1NiIsInR5cCI6IkpXVCJ9.eyJSb2xlIjoiVE9LRU4iLCJJbnZvbHZlZFBhcnR5S
WQiOiIyODgiLCJJbnZvbHZlZFBhcnR5SWRUeXBlIjoiMyIsIkNyZWRlbnRpYWxTdGF0
dXMiOiIxIiwiZXhwIjoxNjcyNDA3NjQyLCJpc3MiOiJibmIuY29tLmJvIn0.70i-
0y7h_Z2za8HO8ePEQt2mqL7YJmPOsxOcoGSG39s' \ 
--header 'Content-Type: application/json' \ 
--data-raw '{   
    "qrId": "23062301001000001030", 
    "scheduleStatus": 1, 
    "installments": [ 
                { 
                  "id":1, 
                  "amount":1000, 
                  "scheduledDate":"2023-06-23 15:30" 
                }] 
}' 
 
15. WEBHOOK 
 
Eventos que deben ser configurados por el cliente, para que pueda recibir las 
notificaciones de todas las transacciones mencionadas en este apartado. 
 
15.1 Método ENROLL 
 
Se utiliza para notificar cuando un QR de domiciliación es aceptado desde una EIF. 
 
15.1.1 Entrada 
 
Property DataType Description 
qrId string Código único asignado al QR de  
domiciliación. 
currencyCode short 
Moneda que se va a utilizar para la 
transacción: 
- 1: Bolivianos, 


<!-- PAGE 32 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
- 2: Dólares . 
Determina que cuenta del cliente será 
utilizada para efectuar los abonos 
correspondientes. 
amount double Monto total de la domiciliación. 
accountType short 
Tipo de cuenta: 
1: Cuenta de ahorro/corriente. 
2: Cuenta de billetera móvil.  
accountNumber string Número de cuenta del destinatario, 
persona que acepta la domiciliación. 
reference string(60) Descripción del servicio a domiciliar. 
accountHolderId string(15) Número de documento de identidad del 
destinatario. 
accountHolder string Nombre del titular destinatario. 
serviceCode string(20) Código de servicio o contrato que 
identifica el servicio a domiciliar. 
originBankId short Corresponde a la entidad financiera del 
cual se realizó el débito. 
 
15.1.2 Salida 
 
Property DataType Description 
success bool Obtendrá como resultado correcto: true, 
caso contrario: false. 
message string Descripción detallada del código de 
respuesta proporcionado. 
status short Código de respuesta del servicio. 
 
 
15.1.3 Ejecutar 
 
Content-Type application/json 
 
Body 
{ 
"qrId":"12546542132121”, 
 "currencyCode":1, 
 "amount":1000, 
 "accountType":1, 
 "accountNumber":"15012121212”, 
 "reference":"Pago de lavadora”, 
 "accountHolderId":"655656”, 
 "accountHolder":"Cliente destinatario”, 
 "originBankId":1, 


<!-- PAGE 33 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
 "serviceCode":"XJKSKS” 
} 
 
 Response 200 OK  
{ 
    "success": true, 
    "message": "OK", 
    "status": 100 
} 
 
 
15.2 Método PAYMENT 
 
Se utiliza para notificar cuando un cobro de domiciliación es efectuado o rechazado 
desde una EIF. 
 
15.2.1 Entrada 
 
Property DataType Description 
qrId string Código único asignado al QR de  
domiciliación. 
installmentId int Identificador de la cuota. 
amount double Monto de la cuota pagada. 
reference string Descripción del servicio a domiciliar. 
serviceCode string Código de servicio o contrato que identifica 
el servicio a domiciliar. 
originBankId short Corresponde a la entidad financiera del 
cual se realizó el débito. 
originName string Corresponde al titular de la cuenta  
voucherId string 
Código de Notificación, alfanumérico de 
10 caracteres que identifica la 
transacción. 
transactionDateTime string Fecha y Hora en la que la transacción fue 
reportada. 
 
 
15.2.2 Salida 
 
Property DataType Description 
success bool Obtendrá como resultado correcto: true, 
caso contrario: false. 
message string Descripción detallada del código de 
respuesta proporcionado. 


<!-- PAGE 34 -->

  DOCUMENTACIÓN DE SERVICIOS OPEN BANKING - DOMICILIACIÓN 
 
 
 
 
status short Código de respuesta del servicio. 
 
15.2.3 Ejecutar 
 
Content-Type application/json 

Body 
{ 
 "qrId":"ACJSS1231", 
 "installmentId ":1, 
 "amount":1000, 
 "reference":"Pago de lavadora”, 
 "serviceCode":"XJKSKS”, 
 "originBankId":1, 
 "originName":"Perez Juan", 
 "voucherId":"0", 
 "transactionDateTime":"19/12/2022 17:30:15" 
} 
 
 Response 200 OK  
{ 
    "success": true, 
    "message": "OK", 
    "status": 100 
} 
 
 