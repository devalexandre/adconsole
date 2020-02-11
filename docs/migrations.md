# Migrations
As migrations chegaram em nossa ferramenta de linha de comando,
para termos uma estabilidade e qualidade selecionamos o pacote usado no cake framework
chamado [phinx](https://phinx.org/) um projeto muito bom que nos permite
usar migrate via linha de comando.

temos aqui um resumo de como usaremos em nosso projero, a documentação completa
pode ser lida em [http://docs.phinx.org](http://docs.phinx.org/en/latest/index.html)

## Instalação
para fazer a instalação seguindo a ideia do adianti framework podemos escolher o local de instalacao
o padrão é ser na raiz do projeto ou pode usar o -c para dizer onde esta a configuração.


```
php vendor/adconsole init 
```