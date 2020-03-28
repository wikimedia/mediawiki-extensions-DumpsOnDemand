# DumpsOnDemand

DumpsOnDemand allows users to request and download database dumps on the wiki. Database dumps can be downloaded from Special:RequestDump. If the user has the `dumpsondemand` right, they can request a new dump whenever they like.

DumpsOnDemand is based on the [Dumps](https://github.com/Wikia/app/tree/dev/extensions/wikia/WikiFactory/Dumps) sub-extension for Wikia's WikiFactory extension

## Flavours
DumpsOnDemand provides two dumps: a dump containing only the current revisions, suitable for bot use and a dump containing all revisions, suitable for archiving.

## Compression
DumpsOnDemand supports three compressed output formats: Gzip (`gz`), Bzip2 (`bz2`) and Zip (`zip`), provided the required PHP extension is installed. If none are installed, DumpsOnDemand will fallback to regular uncompressed dumps.

## Installation
* Add `wfLoadExtension( 'DumpsOnDemand' );` to your LocalSettings.php file.
* Assign the `dumpsondemand` right to any user that should be permitted to request a dump.
* Set up a job runner to run `DatabaseDumpGeneration` jobs (`php maintenance/runJobs.php --type DatabaseDumpGeneration`), or enable `$wgDumpsOnDemandUseDefaultJobQueue`.

### Configuration options
* `$wgDumpsOnDemandUseDefaultJobQueue` - This setting will make the jobs used by DumpsOnDemand execute unconditionally. By default, none of the dump jobs are run, unless specified by the job runner. Enabling this setting is only recommended for small wikis or wikis that have sufficient job runner capacity.
* `$wgDumpsOnDemandCompression` - This setting configures which compression format should be used to compress the dumps. By default DumpsOnDemand chooses an algorithm based on the available PHP extension. An invalid option will result regular dumps without compression. Supported options are:
  * `gz` for GZip
  * `bz2` for BZip2
  * `zip` for Zip
* `$wgDumpsOnDemandRequestLimit` - This setting configures the time between subsequent dump requests. It specifies an amount in seconds that should have passed before a new dump can be requested. Users with the `dumpsondemand-limit-exempt` right can ignore this restriction.
* `$wgDumpsOnDemandFileBackend` - This setting specifies an ObjectFactory spec for a FileBackend instance. The provided object will be used by DumpsOnDemand to write the dumps too and read the urls from. DumpsOnDemand only provides a backend that writes to `$wgUploadDirectory`, but you can add your own by extending the `FileBackend` class and specifying it in this setting.

### User rights
DumpsOnDemand adds three user rights:
* `dumpsondemand` - This user right allows users to request a new dump on Special:RequestDump.
* `dumpsondemand-limit-exempt` - This user right allows users to ignore the time limit between dump requests. Users must still have the `dumpsondemand` right to request a new dump.
* `dumprequestlog` - This user right allows users to view the database dump request log.

## Inner workings
DumpsOnDemand generates the dumps using the JobQueue. Given that the creation of a database dump can take a long time, DumpsOnDemand jobs are not executed along with the regular jobs by default. This can be disabled by setting `$wgDumpsOnDemandUseDefaultJobQueue` to `true`, but that is only recommended for small wikis or wikis with sufficient job running capacity.

The job itself is split into two parts: one for generating a dump containing all revisions, and one for generating a dump with only the latest revisions. 

Dumps are generated similarly to `dumpBackup.php`, using the WikiExporter class. Unlike that maintenance script, the job exports directly to a file, without any additional programs required to be installed on the web server. DumpsOnDemand creates the equivalent of `dumpBackup.php --full --uploads` and `dumpBackup.php --current --uploads`.

## Advanced usages
### Other backends
DumpsOnDemand only supports one file backend out of the box: the LocalFileBackend, which writes and serves the dumps from the upload directory. Custom file backends, such as a backend linked to a cloud provider like Amazon AWS can be added by extending from the `FileBackend` class.

### Manual dump creation
Indirectly, DumpsOnDemand also supports manual dump creation. Only users that have the `dumpsondemand` user right are allowed to request new dumps. By not granting this right to any user group, no one will be allowed to request a new dump. Dumps can then be manually generated and placed in the upload directory, or, using a custom `FileBackend` class, any other location.
