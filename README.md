# LaraCrud
Generate a full CRUD ready to deploy application from your console
## Documentation

### Installation
`composer global create-project -s dev laztopaz/laracrud`

### How to use
- On your current Laravel project directory, type or copy php `~/.composer/laracrud/laracrud make:crud [name]`
it has come parameters you should be aware of.

- `--m=[create|update]` can be passed to the command if you want a new migration file or you just
want to update the existing schema.
- `--g=[model|migration]` can also be passed to the command in case you only want to generate either
a model or a migration. Without passing this parameter, the default mode will generate
both model and migration files.

- `--f[directy name]` You can optionally pass it if your model folder is not app/Models. Otherwise, it will create or append 
the new file to the app/Models folder
