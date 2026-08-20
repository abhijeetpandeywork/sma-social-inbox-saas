const { Client } = require('ssh2');

const config = {
    host: '147.93.23.184',
    port: 65002,
    username: 'u406313474',
    password: 'Gaurav@20221',
};

const repoUrl = 'https://github.com/abhijeetpandeywork/sma-social-inbox-saas.git';
const targetDir = '/home/u406313474/domains/sma.digitalrubix.site/public_html';

console.log(`Connecting to Hostinger SSH server ${config.host}:${config.port}...`);

const conn = new Client();

conn.on('ready', () => {
    console.log('SSH Connection established successfully!');

    const commands = [
        `mkdir -p ${targetDir}`,
        `cd ${targetDir} && if [ ! -d ".git" ]; then git init && git remote add origin ${repoUrl} && git fetch origin && git checkout -b main origin/main -f; else git fetch origin && git reset --hard origin/main; fi`,
        `cd ${targetDir} && cp -n .env.example .env`,
        `cd ${targetDir} && composer install --no-dev --optimize-autoloader`,
        `cd ${targetDir} && if ! grep -q "^APP_KEY=base64" .env; then php artisan key:generate --force; fi`,
        `cd ${targetDir} && php artisan migrate --force`,
        `cd ${targetDir} && php artisan app:reset-demo-data`,
        `cd ${targetDir} && php artisan config:cache && php artisan route:cache && php artisan view:cache`,
        `cd ${targetDir} && php artisan --version`
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
