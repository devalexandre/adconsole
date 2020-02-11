
#### Associação
```
--patch out model default app/models
--fields of model
-s, --assosiacao[=ASSOSIACAO]

php adcconsole/console app:model:create Customer  --fields=nome,sobre --patch=teste -s contacts
```


#### Composicao
```
--patch out model default app/models
--fields of model
-c, --composition[=COMPOSITION]


php adcconsole/console app:model:create Customer  --fields=nome,sobre --patch=teste -c contacts
```


#### Agregação
```
--patch out model default app/models
--fields of model
 --pivot[=PIVOT] default $name+$agregate
-a, --aggregate[=AGGREGATE]

php adcconsole/console app:model:create Customer  --fields=nome,sobre --patch=teste  -a contacts 
```

