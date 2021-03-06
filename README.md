Debuger
=======

The error handler and dump variables.

![function p()](docs/web-p.gif)

## Installation

Download repository:

    git clone https://github.com/Professionali/debuger.git

Registr in `php.ini`:

    auto_prepend_file = "<path_to_debuger>\debuger\debuger.php"


## Using

Show variable dump:

    p($my_var);

### In Web mode

Notice

![notice in web](docs/web-notice.jpg)

Exception

![exception in web](docs/web-exception.jpg)

Exception trace

![exception trace in web](docs/web-exception-trace.jpg)

### In CLI mode

Notice

![notice in cli](docs/cli-notice.jpg)

Exception

![exception in cli](docs/cli-exception.jpg)

Variable dump in CLI

![function p() in cli](docs/cli-p.jpg)
