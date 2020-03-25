<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class SetupVueJs extends Command
{
    use VueFilesTxt;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:vuejs {delete_old=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'setup all vuejs needed files for multi page application';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Filesystem $fs)
    {
        // extract root directory
        $root = $fs->dirname($fs->dirname($fs->dirname(__DIR__)));
        $js = $root . '/resources/js/';

        // move to resources/js directory
        chdir($js);

        // create required directories

        if ($fs->exists($js . 'pages')) {
            $this->info('All files was created succefully');
            return;
        }

        // create index ts file
        touch('app.ts');
        $fs->put('app.ts', self::$App);

        mkdir('pages');
        chdir('pages');

        $fs->put('index-template.html', self::$indexHtml);
        $fs->put('Super.ts', self::$super);
        $fs->put('Home.ts', self::$home);

        chdir('../');

        // remove useless files if no argument
        if (!!(int) $this->argument('delete_old')) {
            $fs->delete('app.js');
            $fs->delete('bootstrap.js');

            // remove all components
            array_map('unlink', glob('components/*.*'));
        }

        // go to root directory
        chdir('../../');
        // create tsconfig file
        $fs->put('tsconfig.json', self::$tsconfig);
        // update webpack mix configration
        $fs->put('webpack.mix.js', self::$mix);

        $this->info('updated tsconfig and webpack, please update your website name in webpack.mix file');

        // updating package.json
        $package = json_decode($fs->get('package.json'));

        $dev = $package->devDependencies;

        if (!isset($package->dependencies)) $package->dependencies = (object) [];

        $req = $package->dependencies;

        // add to dev
        $dev->{'ts-loader'} = '^6.2.2';
        $dev->{'browser-sync'} = '^2.26.7';
        $dev->{'browser-sync-webpack-plugin'} = '^2.0.1';

        // add to requirments
        $req->{'bootstrap.native'} = '^2.0.27';
        $req->{'typescript'} = '^3.8.3';
        $req->{'vue-class-component'} = '^7.2.3';
        $req->{'vue-property-decorator'} = '^8.4.1';

        // print formatted json
        $fs->put('package.json', (collect($package))->toJson(JSON_PRETTY_PRINT));

        $this->info('package.json updated successfully');

        $this->info('please add this code in your layout file');
        $this->info(self::$layout);
    }
}
