<?php
namespace Deployer;

require 'recipe/symfony4.php';
// musste memory-limit setzen
set('bin/php', function () {
    return '/usr/bin/php72 -d memory_limit=-1';
});


// set('bin/console', function() {
//     return '{{release_path}}/bin/console';
// });

// Project name
set('application', 'msc_inerface');

// user: Musste gesetzt werden
set('http_user', 'u2416246');

// Project repository
set('repository', 'git@bitbucket.org:roelfsche/msc.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true); 

// Shared files/dirs between deploys 
 add('shared_files', ['.env'/*, 'config/packages/doctrine.yml'*/]);
// add('shared_dirs', []);

// Writable dirs by web server 
// add('writable_dirs', []);


// Hosts
host('maridis-test')
    ->stage('test')
    ->roles('app')
    ->port(22)
    ->configFile('~/.ssh/config')
    ->identityFile('~/.ssh/id_rsa')
    ->forwardAgent(true)
    ->multiplexing(true)
    ->addSshOption('UserKnownHostsFile', '/dev/null')
    ->addSshOption('StrictHostKeyChecking', 'no')
    ->set('deploy_path', '~/htdocs/msc/symfony/test');    
    
host('maridis-prod')
    ->stage('testing')
    ->roles('app')
    ->port(22)
    ->configFile('~/.ssh/config')
    ->identityFile('~/.ssh/id_rsa')
    ->forwardAgent(true)
    ->multiplexing(true)
    ->addSshOption('UserKnownHostsFile', '/dev/null')
    ->addSshOption('StrictHostKeyChecking', 'no')
    ->set('deploy_path', '~/htdocs/msc/symfony/prod');    
// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

// before('deploy:symlink', 'database:migrate');
