// Copies uploads (storage/app/public, which git ignores) to the server over SSH.
//   npm run sync:media                      upload to live
//   npm run sync:media -- --dry-run         list what would be sent
//   npm run sync:media -- --target=staging
// Settings come from .deploy.env (git-ignored; copy .deploy.env.example). Needs ssh and tar, both built into Windows 10/11.
import { spawn } from 'node:child_process';
import { readFileSync, readdirSync, statSync, existsSync } from 'node:fs';
import { join, dirname, relative, sep } from 'node:path';
import { fileURLToPath } from 'node:url';
import os from 'node:os';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const args = process.argv.slice(2);
const dry = args.includes('--dry-run');
const target = (args.find((a) => a.startsWith('--target=')) || '--target=live').slice(9).toUpperCase();

const envFile = join(root, '.deploy.env');
if (!existsSync(envFile)) { console.error('Missing .deploy.env - copy .deploy.env.example and fill it in.'); process.exit(1); }
const env = {};
for (const line of readFileSync(envFile, 'utf8').split(/\r?\n/)) {
    const m = line.match(/^\s*([A-Z_]+)\s*=\s*([^#]*?)\s*(?:#.*)?$/);
    if (m) env[m[1]] = m[2].replace(/^["']|["']$/g, '');
}
const host = env[`${target}_HOST`], user = env[`${target}_USER`], app = (env[`${target}_PATH`] || '').replace(/\/$/, '');
const port = env[`${target}_PORT`] || '22';
const key = (env[`${target}_KEY`] || '').replace(/^~/, os.homedir());
if (!host || !user || !app) { console.error(`Set ${target}_HOST, ${target}_USER and ${target}_PATH in .deploy.env`); process.exit(1); }
if (/123\.45\.67\.89|u123456789|YOURSITE|\/USER\/|your_key|^USER$/.test([host, user, app, key].join(' '))) { console.error('.deploy.env still has the example values (123.45.67.89, u123456789, YOURSITE, your_key). Put in your real SSH details from hPanel > Advanced > SSH Access.'); process.exit(1); }
if (key && !existsSync(key)) { console.error(`Key file not found: ${key}. Fix ${target}_KEY in .deploy.env or leave it empty to use your default key / password.`); process.exit(1); }

const src = join(root, 'storage', 'app', 'public');
const files = [];
(function walk(d) {
    for (const n of readdirSync(d)) {
        const p = join(d, n);
        if (statSync(p).isDirectory()) walk(p); else if (n !== '.gitignore') files.push(relative(src, p).split(sep).join('/'));
    }
})(src);
console.log(`Uploads to send to ${target.toLowerCase()} (${user}@${host}:${app}/storage/app/public): ${files.length} files`);
if (dry) { files.forEach((f) => console.log(f)); process.exit(0); }

const dest = `${app}/storage/app/public`;
const sshArgs = ['-p', port, '-o', 'StrictHostKeyChecking=accept-new', ...(key ? ['-i', key] : []), `${user}@${host}`,
    `mkdir -p '${dest}' && tar -C '${dest}' -xf - && find '${dest}' -type f -exec chmod 644 {} + && find '${dest}' -type d -exec chmod 755 {} +`];
const tar = spawn('tar', ['-C', src, '--exclude=.gitignore', '-cf', '-', '.'], { stdio: ['ignore', 'pipe', 'inherit'] });
const ssh = spawn('ssh', sshArgs, { stdio: ['pipe', 'inherit', 'inherit'] });
ssh.stdin.on('error', () => {});   // the connection failed: the message from ssh below says why
tar.stdout.pipe(ssh.stdin);
tar.on('error', (e) => { console.error('tar failed:', e.message); process.exit(1); });
ssh.on('error', (e) => { console.error('ssh failed:', e.message); process.exit(1); });
ssh.on('close', (code) => { console.log(code === 0 ? `Done. ${files.length} files are on the server.` : `ssh exited with code ${code}`); process.exit(code ?? 1); });
