Using the testing framework for unit tests
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

While testing new features in the Seminar Manager extension, we were
in need of tools that allow us to easily create and delete dummy
records in the database. We designed a small “testing framework” as we
call it.


Overview
""""""""

The testing framework enables you to easily

- Add and change dummy records with defined record data that uses real
  UIDs

- Remove single records from a database table

- Add dummy relations to an m:n table

- Remove single relations from a database table

- Create a fake front end for testing front-end plugins


Before you start
""""""""""""""""

You need the following stuff before you start:

- This extension (tx\_oelib) must be installed

- You'll have to write your own test suite for your extension


Write a test suite
""""""""""""""""""

Now write your test suite. Keep in mind that the file must be located
under /tests/ in your extension's directory, the filename must end
with “testcase.php” and the names of all test methods must start with
“test”.

The testing framework must be instantiated once per extension that
you're about to write tests. This is mainly for security reasons! If
you instantiate it for “tx\_seminars”, it will not allow you to
add/remove any record on any table outside of the tx\_seminars scope
(this means all table names must start with “tx\_seminars” in that
case).

Here's a very short example that might help you to integrate our
testing framework into your tests. It's taken 1:1 from the tests for
the  *Seminar Manager* extension.We're not able to provide a full
introduction about unit testing and PHPUnit at all. So please read one
of the many good documentations regarding this topic.

::

   <?php
   class tx_seminars_categoryTest extends tx_phpunit_testcase {
     private $fixture;
     private $testingFramework;

     /** UID of the fixture's data in the DB */
     private $fixtureUid = 0;

     public function setUp() {
             $this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
             $this->fixtureUid = $this->testingFramework->createRecord(
                     SEMINARS_TABLE_CATEGORIES,
                     array('title' => 'Test category')
                   );
           }

     public function tearDown() {
             $this->testingFramework->cleanUp();
           }

     public function testGetTitle() {
             $this->fixture = new tx_seminars_category($this->fixtureUid);

             $this->assertEquals(
                     'Test category',
                     $this->fixture->getTitle()
                   );
           }
   }
   ?>

You can have a deeper look into our example in the subdirectory
“tests/” of oelib: All methods of this testing framework are covered
with at least one unit test. So it will be easy to see how these tools
can be used for your own unit tests.
