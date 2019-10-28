MAPSTORE - is a geolocation sites constructor.

==============================================


FEATURES:
1. High speed processing.
2. Mobile and desctop versions available.
3. Extensibility by adding new modules.
4. Several sites can be serviced by one constructor.


DIRECTORY DESCRIPTION:
application - keeps core and other background code
    components - classes perform simple functions (for which there is no need to create separate modules)
    config - generic settings of constructor
    controllers - MVC controllers
    functions - single freely usable functions (in case when there is no need to create a separate class in components directory for this need)
    models - MVC database and form models
    modules - complex unit, can be functionally complete and closed (using only inner data and database) or be a part of framework. It allows to expand constructor and sites opportunities.
    services - sites data directory
    vendor - core of framework
    views - MVC views and layouts
css - css files directory
files - files used on site (photos, archives etc.) directory
img - frontend images (pictures, icons etc.) directory
javascript - javascript files directory
log - log data directory
migration_queries - migration queries directory
shell - console files directory


EXAMPLES:
http://world-landmarks.ru


DEVELOPER:
Ladonin Alexander <ladonin85@mail.ru>