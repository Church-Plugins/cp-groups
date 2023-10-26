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

#### 1.1.7
* Add setting to disable the Groups archive page

#### 1.1.6
* Update styles for archive page

#### 1.1.5
* Update core

#### 1.1.4
* Add improvements to the Honeypot

#### 1.1.3
* Add integration with CP Connect when pulling groups from Ministry Platform
* Add Cc field to contact form

#### 1.1.2
* Fix bug with single page contact action
* Support resources in group modal

#### 1.1.0
* Add contact form for Group Leaders and Registration

#### 1.0.2
* Add facets for "Child Friendly", "Wheelchair Accessible", and "Group is Full"

#### 1.0.1
* Update stying for archive page
* Add settings page and default thumbnail

#### 1.0.0
* Initial release
