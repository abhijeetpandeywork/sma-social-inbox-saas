const { Client } = require('ssh2');

const config = {
    host: '147.93.23.184',
    port: 65002,
    username: 'u406313474',
    password: 'Gaurav@20221',
};

const repoUrl = 'https://github.com/abhijeetpandeywork/sma-social-inbox-saas.git';
const targetDomain = 'sma.digitalrubix.site';

console.log(`Connecting to Hostinger SSH server ${config.host}:${config.port}...`);

const conn = new Client();

conn.on('ready', () => {
    console.log('SSH Connection established successfully!');

    // Remote commands to deploy Laravel app for sma.digitalrubix.site
    const commands = [
        `mkdir -p domains/${targetDomain}/public_html`,
        `cd domains/${targetDomain}/public_html && if [ ! -d ".git" ]; then git clone ${repoUrl} .; else git pull origin main; fi`,
        `cd domains/${targetDomain}/public_html && cp .env.example .env`,
        `cd domains/${targetDomain}/public_html && composer install --no-dev --optimize-autoloader`,
        `cd domains/${targetDomain}/public_html && php artisan key:generate --force`,
        `cd domains/${targetDomain}/public_html && php artisan migrate --force`,
        `cd domains/${targetDomain}/public_html && php artisan db:seed --force`,
        `cd domains/${targetDomain}/public_html && php artisan config:cache && php artisan route:cache && php artisan view:cache`,
        `cd domains/${targetDomain}/public_html && php artisan --version`
    ].join(' && ');

    console.log('Executing deployment remote command sequence...');

    conn.exec(commands, (err, stream) => {
        if (err) {
            console.error('Execution Error:', err);
            conn.end();
            return;
        }

        stream.on('close', (code, signal) => {
            console.log(`Remote command process exited with code ${code}`);
            conn.end();
        }).on('data', (data) => {
            console.log(`STDOUT:\n${data.toString()}`);
        }).stderr.on('data', (data) => {
            console.error(`STDERR:\n${data.toString()}`);
        });
    });
}).on('error', (err) => {
    console.error('SSH Connection Failed:', err);
}).connect(config);
