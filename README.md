# Church Plugins Groups
Church Groups plugin.

##### First-time installation  #####

- Copy or clone the code into `wp-content/plugins/cp-groups/`
- Run these commands
```
composer install
npm install
cd app
npm install
npm run build
```

##### Dev updates  #####

- There is currently no watcher that will update the React app in the WordPress context, so changes are executed through `npm run build` which can be run from either the `cp-groups`

### Change Log

#### 1.0.0
* Initial release
