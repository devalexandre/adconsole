# Rest

## Instalação

Para instalar a rest usando o adconsole é muito simples

```

php vendor/adconsole rest:install

```

feito isso ele ira gerar a rest.php na raiz do projeto tambem ira colocar a configuração
da rest no arquivo adconfig dentro da pasta app/config

## Usando

Para pegar a token do usuario

``` js
$.ajax({ 
type: 'POST', 
url: 'www.meusistema.local/rest.php', 
data: { 
"username": "admin", 
"password": "admin",
}, 
dataType: 'json', 
success: function (response) { 
console.log(response.data); 
} 
}); 

```

Usando, aposter a token 

```  js
$.ajax({ 
type: 'POST', 
url: 'www.meusistema.local/rest.php', 
    data: { 
        "class":"Home",
        "method":"index",
        "name":"Alexandre souza" // parametro
    }, 
    headers: {
            'Authorization' : 'Bearer '+token
            },
    dataType: 'json', 
    success: function (response) { 
    console.log(response.data); 
} 
}); 

```
