#Yii Mangan

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Maslosoft/Mangan/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Maslosoft/Mangan/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Maslosoft/Mangan/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Maslosoft/Mangan/?branch=master)
<img src="https://travis-ci.org/Maslosoft/Mangan.svg?branch=master" style="height:18px"/>
[![HHVM Status](http://hhvm.h4cc.de/badge/maslosoft/mangan.svg)](http://hhvm.h4cc.de/package/maslosoft/mangan)

######Advanced MongoDB document mapper for Yii

This extension originally started as a fork of [YiiMongoDbSuite](canni.github.com/YiiMongoDbSuite "YiiMongoDbSuite"),
written by [canni](https://github.com/canni "canni") and further improved by several developers ([mintao](https://github.com/mintao "mintao"), et al).
YiiMongoDbSuite originally started as a fork of [MongoRecord](www.yiiframework.com/extension/mongorecord "MongoRecord")
extension written by [tyohan](http://www.yiiframework.com/user/31/ "tyohan"),
to fix some major bugs, and add full featured suite for [MongoDB](http://www.mongodb.org "MongoDB") developers.

The current version is 3.0.0-beta.1

## The Key Feature List:

### Features Covered From Standard Yii Implementations

- Support of using Class::model()->find / findAll / count / countByAttributes and other Yii ActiveRecord syntax.
- Named scopes, along with default scope and parameterized scopes, just like in AR.
- Ready to go out-of-box *EFFICIENT* DataProvider, witch use native php db driver sort, limit and offset features for returning results!
- Model classes and embedded documents inherit from CModel, so you can use every class witch can handle of CModel (ie: Gii form generator)
- Relations support *idea/concept/example*.
- **Support for generating CRUD for Document models, with Gii!**.
- **Support for generating mongo document models from existing SQL tables!**.
- Use MongoDB for LogRoute and HttpSession.
- Easy to use criteria object, you don't have to create complex MongoDB query arrays.
- **Fixtures manager, that can replace the Yii default one, and work with Mongo model.**

### MongoDB Related Feature List

- Support of schema-less documents with Yii standard rules and validation features
- Embedded document and arrays of embedded documents support
- Ability to set FSync and/or Safe flag of DB write operations on different scopes, globally, on model level, and on single model object
- **Ability to use efficient MongoDB Cursors instead of raw arrays of results, returned by the findAll* methods**
- MongoDB GridFS feature support, thanks to work of Jose Martinez and Philippe Gaultier
- Support for using any other than _id field as a Primary Key, for a collection
- Automated efficient index definition for collections, per model
- Support "Soft" documents, documents that do not have fixed list of attributes
- **Ability to do *Extreme Efficent* document partial updates, that make use of MongoDB `$set` operator/feature**

### Yii Addendum Related Feature List

- Easy model definition
- Clean view of any model field options/properties
- Lightweight and extensible model metadata

## Limitations
- The main limitations are only those present in MongoDB itself, like the 16mb data transfer limit.
- In it's current incarnation, This extension does NOT work with the "$or" criteria operator. When we get it working we will remove this line and add an example.

## Requirements

- Yii 1.1.14+ is recommended.
- MongoDB 2.4.0+ is recommended. Untested with older versions.
- Mongo PHP Driver 1.4.5+
- PHP 5.5+
- composer

## Setup

Use composer to install extension:

	composer require maslosoft/mangan <version>

In your protected/config/main.php config file. Add `mongoDB` array
for your database in the components section, and add the following to the file:

	'mongodb' => [
		'connectionString' => 'mongodb://user:password@mongo-db-host.example.com',
		'dbName' => 'db_name',
		'class' => 'Maslosoft\Mangan\MongoDB'
	],

- ConnectionString: 'localhost' should be changed to the ip or hostname of your host being connected to. For example
  if connecting to a server it might be `'connectionString' => 'mongodb://username@xxx.xx.xx.xx'` where xx.xx.xx.xx is
  the ip (or hostname) of your webserver or host.
- dbName: The database name, where your collections will be be stored in.
- fsyncFlag If set to true, this makes mongodb make sure all writes to the database are safely stored to disk (true by default).
- safeFlag If set to true, mongodb will wait to retrieve status of all write operations, and check if everything went OK (true by default).
- useCursors If set to true, extension will return `Cursor` instead of raw pre-populated arrays, from findAll* methods (defaults to false for backwards compatibility).

That's all you have to do for setup. You can use it very much like the active record.
For example:

	<?php
    $client = new Client();
    $client->first_name='something';
    $client->save();
    $clients = Client::model()->findAll();

## Basic Usage

Just define following model:

	<?php
	use Maslosoft\Mangan\Document;
	
    class User extends Document
    {
      /**
       * @Label('User login')
       * @RequiredValidator
       */
      public $login;
      
      /**
       * @Label('Full name')
       * @LengthValidator(max => 255)
       */
      public $name;
      
      /**
       * @Label('Password')
       * @RequiredValidator
       * @LengthValidator(min => 6, max => 20)
       */
      public $password;
    }


And that's it! Now start using this User model class like standard Yii AR model.

## Embedded Documents

*NOTE: For performance reasons embedded documents should extend from `EmbeddedDocument` instead of `Document`.*

`EmbeddedDocument` is almost identical as `Document`, in fact `Document` extends from `EmbeddedDocument`
and adds to it the DB connection and related functions.

So if you have a User.php model, and an UserAddress.php model which is the embedded document.
Lest assume we have following embedded document:

	<?php
	use Maslosoft\Mangan\EmbeddedDocument;
	
    class UserAddress extends EmbeddedDocument
    {
      /**
       * @Label('City')
       * @LengthValidator(max => 255)
       */
      public $city;
      
      /**
       * @Label('Street')
       * @LengthValidator(max => 255)
       */
      public $street;
      
      /**
       * @Label('Home number')
       * @LengthValidator(max => 255)
       */
      public $house;
      
      /**
       * @Label('Apartment number')
       * @LengthValidator(max => 10)
       */
      public $apartment;
      
      /**
       * @Label('Postal code')
       * @LengthValidator(max => 6)
       */
      public $zip;
    }

Now we can add this document to our User model from previous section:

	<?php
	use Maslosoft\Mangan\Document;
	
    class User extends Document {
      ...

      /**
       * @Embedded('UserAddress')
       */
      public $address = null;

      ...
    }

And using it is as easy as π!

	<?php
    $client = new Client;
    $client->address->city='New York';
    $client->save();

This will automatically call validation for the model and all its embedded documents.
You can even nest embedded documents in embedded documents and array of embbedded document, also mix any embedded document types!
*IMPORTANT*: This mechanism uses recurrency, and **will not handle circular nesting**, so use this feature with care.

## Arrays

**Simple arrays**

- Just define a property for an array, and store an array in it.

	<?php
	...
	/**
	 * @SafeValidator
	 * @var string[]
	 */
	public $addresses = [];

**Arrays of embedded documents**

- Just need to add @EmbeddedArray annotation


	<?php
	...
    /**
     * @EmbeddedArray('UserAddress')
	 * @var UserAddress[]
     */
    public $addresses = [];
    

So for the user, if you want them to be able to save multiple addresses, you can do this:

	<?php
    $c = new Client;
    $c->addresses[0] = new ClientAddress;
    $c->addresses[0]->city='NY';
    $c->save(); // will handle validation of array too


Then you can loop addresses:

	<?php
    $c = Client::model()->find();
    foreach($c->addresses as $addr)
    {
        echo $addr->city;
    }

## Querying

This is one of the things that makes this extension great. It's very easy to query for the objects you want.

	<?php
    // simple find first. just like normal AR.
    $object = ModelClass::model()->find()


Now suppose you want to only retrieve users, that have a status of 1 (active). There is an object just for that, making queries easy.

	<?php
	use Maslosoft\Mangan\Criteria;
	
    $c = new Criteria;
    $c->status('==', 1);
    $users = ModelClass::model->findAll($c);


and now $users will be an array of all users with the status key in their document set to 1. This is a good way to list only active users.
What's that? You only want to show the 10 most recent activated users? Thats easy too.

	<?php
    use Maslosoft\Mangan\Criteria;
	
    $c = new Criteria;
    $c->active('==', 1)->limit(10);

    $users = ModelClass::model->findAll($c);


It's that easy. In place of the 'equals' key, you can use any of the following operators:


    - 'greater'   | >
    - 'greaterEq' | >=
    - 'less'      | <
    - 'lessEq'    | <=
    - 'notEq'     | !=, <>
    - 'in'        |
    - 'notIn'     |
    - 'all'       |
    - 'size'      |
    - 'exists'    |
    - 'type'      | // BSON type see mongodb docs for this
    - 'notExists' |
    - 'mod'       | %
    - 'equals'    | ==
    - 'where'     | // JavaScript operator

*NOTICE: the $or operator in newer versions of mongodb does NOT work with this extension yet. We will add it to the list above when it is fixed. Newer versions of MongoDB will work, just not the $or operator.
For examples and use for how to use these operators effectively, use the [MongoDB Operators Documentation here](http://www.mongodb.org/display/DOCS/Advanced+Queries).

Here are a few more examples for using criteria:

	<?php
	use Maslosoft\Mangan\Criteria;
	
    $criteria = new Criteria;

    // find the single user with the personal_number == 12345
    $criteria->personal_number('==', 12345);
	
    // OR like this:
    $criteria->personal_number = 12345;

    $user = User::model->find($criteria);

    // find all users in New York. This will search in the embedded document of UserAddress
    $criteria->address->city('==', 'New York');
    // Or
    $criteria->address->city = 'New York';
    $users = User::model()->findAll($criteria);

    // Ok now try this. Only active users, only show at most 10 users, and sort by first name, descending, and offset by 20 (pagination):
    // note the sort syntax. it must have an array value and use the => syntax.
    $criteria->status('==', 1)->limit(10)->sort(array('firstName' => Criteria::SORT_DESC))->offset(20);
    $users = User::model()->findAll($criteria);

    // A more advanced case. All users with a personal_number evenly divisible by 10, sorted by first name ascending, limit 10 users, offset by 25 users (pagination), and remove any address fields from the returned result.
    $criteria->personal_number('%', array(10, 0)) // modulo => personal_number % 10 == 0
             ->sort(array('firstName' => Criteria::SORT_ASC))
             ->limit(10)
             ->offset(25);
    $users = User::model()->findAll($criteria);

    // You can even use the where operator with javascript like so:
    $criteria->fieldName('where', ' expression in javascript ie: this.field > this.field2');
    // but remember that this kind of query is a bit slower than normal finds.



### Regexp / SQL LIKE Replacemt

You can use native PHP Mongo driver class MongoRegex, to query:

	<?php
	use Maslosoft\Mangan\Criteria;
    
    $criteria = new Criteria;
	
    // Find all records witch have first name starring on a, b and c, case insensitive search
    $criteria->first_name = new MongoRegex('/[abc].*/i');
    $clients = Client::model()->findAll($criteria);
    // see phpdoc for MongoRegex class for more examples


for reference on how to use query array see: http://www.php.net/manual/en/mongocollection.find.php

### Creating Criteria Objects From Arrays

	<?php
	use Maslosoft\Mangan\Criteria;
	
    // Example criteria
    $array = array(
        'conditions'=>array(
        	// field name => operator definition
        	'FieldName1'=>array('greaterEq' => 10), // Or 'FieldName1'=>array('>=', 10)
        	'FieldName2'=>array('in' => array(1, 2, 3)),
        	'FieldName3'=>array('exists'),
        ),
        'limit'=>10,
        'offset'=>25,
        'sort'=>array('fieldName1' => Criteria::SORT_ASC, 'fieldName4' => Criteria::SORT_DESC),
    );
    $criteria = new Criteria($array);
    // or
    $clients = ClientModel::model()->findAll($array);


## Known bugs

- Remember, this is not complete yet. So at this stage, it can have some
- If you find any [please let me know](https://github.com/Maslosoft/YiiMangan/issues).
- As said before, it does not work with the OR operators.


## Resources

 * [Project website](http://maslosoft.com/en/open-source/yii-mangan/)
 * [Project Page on GitHub](https://github.com/Maslosoft/Mangan)
 * [Report a Bug](https://github.com/Maslosoft/Mangan/issues)
 * [MongoDB Documentation](http://www.mongodb.org/display/DOCS/Home)
 * [PHP MongoDB Driver docs](http://www.php.net/manual/en/book.mongo.php)
 * [Standard Yii ActiveRecord Documentation](http://www.yiiframework.com/doc/guide/1.1/en/database.ar)
 * [Yii Addendum](http://maslosoft.com/en/open-source/yii-addendum/)
 * [PHP Addendum library](http://code.google.com/p/addendum/)


## Contribution needed!

- Any help would be great :)


## Acknowledgments

We stand upon the shoulders of giants:

- canni, for leading continued development, bug testing, and merging contributions.
- tyohan, for first inspirations and the extension's concept.
- luckysmack, for big help with testing and documentation.
- Jose Martinez and Philippe Gaultier, for implementing and sharing GridFS support.
- Nagy Attila Gábor, for big help with new functionality and testing.
