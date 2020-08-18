# LaraCrud
Generate a full CRUD ready to deploy application from your console
## Documentation

### What this project currently does
- Let you interactively define your database schema
- Generate a fresh model into your app/Models folder and let you specify your default path.
  - It helps you generate the $fillables, and the $casts arrays to your model
- Generate a migration file for you.

### Installation
- Go to the release page and download the laracrud binary
- Move it to the `sudo mv path/to/laracrud /usr/local/bin/` to run it as a root all the time
- Give it permission `sudo chmod 755 /usr/local/bin/laracrud` to make it executable
- We need to let bash know where the executable file is `nano ~/.bash_profile`

Add this line below to bash_profile and save
- `alias generator="php /usr/local/bin/laracrud"`

and then run this command:
- `source ~/.bash_profile`

And finally you can type `generator` in your terminal and see 

![alt text](https://github.com/olotintemitope/LaraCrud/blob/master/laracrud.png  "Laracrud console")

If you're on mac, you can set the path like

```echo 'export PATH="$PATH:$HOME/.composer/vendor/bin"' >> ~/.bashrc```

This will help you run the command on a global level. After that, you can now run the command anywhere in your terminal
like; `laracrud make:crud [Any alphabetical name]`


### How to use
- From your terminal, navigate to your current Laravel project directory and type or copy php `~/.composer/laracrud/laracrud make:crud [name]`
  - It has some optional parameters you should be aware of, such as;

- `--m=[create|update]` can be passed to the command if you want a new migration file or you just
want to update the existing schema.
- `--g=[model|migration]` can also be passed to the command in case you only want to generate either
a model or a migration. Without passing this parameter, the default mode will generate
both model and migration files.

- `--f[directory]` You can optionally pass it, if your model folder is not in app/Models. Otherwise, it will create or append 
the new file to the app/Models folder

### Supported Field Types
- This project supports all [Laravel migration column types](https://laravel.com/docs/5.5/migrations#creating-columns)
- The default column type is string. Therefore, you can hit the return key if you do not want to
change the type.
- For string and integer field type, you can also omit the length so that it will use the default
length.
