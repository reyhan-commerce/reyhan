#!/usr/bin/env node

/**
 * ==============================================================================
 *   ██████╗ ███████╗██╗   ██╗██╗  ██╗ █████╗ ███╗   ██╗
 *   ██╔══██╗██╔════╝╚██╗ ██╔╝██║  ██║██╔══██╗████╗  ██║
 *   ██████╔╝█████╗   ╚████╔╝ ███████║███████║██╔██╗ ██║
 *   ██╔══██╗██╔══╝    ╚██╔╝  ██╔══██║██╔══██║██║╚██╗██║
 *   ██║  ██║███████╗   ██║   ██║  ██║██║  ██║██║ ╚████║
 *   ╚═╝  ╚═╝╚══════╝   ╚═╝   ╚═╝  ╚═╝╚═╝  ╚═╝╚═╝  ╚═══╝
 *
 *   create-reyhan — Next-Gen Full-Stack Headless Commerce Scaffolder
 *   Laravel 13 + Nuxt 4 + PostgreSQL 17+ + Redis 7+
 * ==============================================================================
 */

import { intro, outro, text, select, password, confirm, spinner, isCancel, cancel, note } from '@clack/prompts'
import pc from 'picocolors'
import { existsSync, cpSync, mkdirSync, writeFileSync, chmodSync } from 'node:fs'
import { resolve, join, basename } from 'node:path'
import { execSync } from 'node:child_process'

const BANNER = pc.green(`
  ██████╗ ███████╗██╗   ██╗██╗  ██╗ █████╗ ███╗   ██╗
  ██╔══██╗██╔════╝╚██╗ ██╔╝██║  ██║██╔══██╗████╗  ██║
  ██████╔╝█████╗   ╚████╔╝ ███████║███████║██╔██╗ ██║
  ██╔══██╗██╔══╝    ╚██╔╝  ██╔══██║██╔══██║██║╚██╗██║
  ██║  ██║███████╗   ██║   ██║  ██║██║  ██║██║ ╚████║
  ╚═╝  ╚═╝╚══════╝   ╚═╝   ╚═╝  ╚═╝╚═╝  ╚═╝╚═╝  ╚═══╝
`)

async function main() {
  console.log(BANNER)
  intro(pc.green(pc.bold('🌿  Reyhan Full-Stack Headless Commerce Installer')))

  const args = process.argv.slice(2)
  const isYes = args.includes('--yes') || args.includes('-y') || args.includes('--non-interactive')

  function getArgValue(name, fallback) {
    const prefix = `--${name}=`
    const found = args.find(a => a.startsWith(prefix))
    if (found) return found.slice(prefix.length)
    const idx = args.indexOf(`--${name}`)
    if (idx !== -1 && args[idx + 1] && !args[idx + 1].startsWith('--')) {
      return args[idx + 1]
    }
    return fallback
  }

  const defaultDir = args.find(a => !a.startsWith('-')) || 'my-reyhan-store'

  let targetDirInput = defaultDir
  let storeName = getArgValue('store-name', 'فروشگاه ریحان')
  let themeColor = getArgValue('theme', 'emerald')
  let dbHost = getArgValue('db-host', '127.0.0.1')
  let dbPort = getArgValue('db-port', '5432')
  let dbName = getArgValue('db-name', 'reyhan_db')
  let dbUser = getArgValue('db-user', 'postgres')
  let dbPass = getArgValue('db-password', '')
  let redisHost = getArgValue('redis-host', '127.0.0.1')
  let redisPort = getArgValue('redis-port', '6379')
  let shouldInitGit = !args.includes('--no-git')
  let shouldInstallDeps = args.includes('--install-deps')

  if (!isYes) {
    // 1. Project Directory
    targetDirInput = await text({
      message: 'Where would you like to create your new store project?',
      placeholder: defaultDir,
      defaultValue: defaultDir,
      validate(val) {
        if (!val || val.trim().length === 0) return 'Project folder name cannot be empty.'
        if (existsSync(resolve(process.cwd(), val))) return `Directory "${val}" already exists.`
      }
    })

    if (isCancel(targetDirInput)) {
      cancel('Setup cancelled.')
      process.exit(0)
    }

    // 2. Store Name
    storeName = await text({
      message: 'What is your store brand name?',
      placeholder: 'فروشگاه ریحان',
      defaultValue: 'فروشگاه ریحان'
    })
    if (isCancel(storeName)) { cancel('Setup cancelled.'); process.exit(0); }

    // 3. Theme Preset
    themeColor = await select({
      message: 'Select the primary brand color theme for Nuxt 4 Storefront:',
      options: [
        { value: 'emerald', label: 'Emerald (زمردی ریحان — تم امضا)', hint: 'Universal clean, fresh & modern' },
        { value: 'indigo', label: 'Indigo (نیلی فناوری)', hint: 'Ideal for tech, electronics & modern retail' },
        { value: 'rose', label: 'Rose (رز لوکس)', hint: 'Ideal for fashion, beauty & luxury goods' },
        { value: 'violet', label: 'Violet (بنفش سلطنتی)', hint: 'Vibrant, creative & modern digital goods' },
        { value: 'neutral', label: 'Zinc (تک‌رنگ مینیمال)', hint: 'Sleek monochrome minimal design' }
      ],
      initialValue: 'emerald'
    })
    if (isCancel(themeColor)) { cancel('Setup cancelled.'); process.exit(0); }

    // 4. BYOD Database Configuration Notice
    note(
      `Reyhan adheres to BYOD (Bring Your Own Database).\n` +
      `It connects to existing PostgreSQL & Redis servers via .env and does NOT install DB software locally.`,
      pc.yellow('Database Architecture Notice')
    )

    dbHost = await text({
      message: 'PostgreSQL Host:',
      placeholder: '127.0.0.1',
      defaultValue: '127.0.0.1'
    })
    if (isCancel(dbHost)) { cancel('Setup cancelled.'); process.exit(0); }

    dbPort = await text({
      message: 'PostgreSQL Port:',
      placeholder: '5432',
      defaultValue: '5432'
    })
    if (isCancel(dbPort)) { cancel('Setup cancelled.'); process.exit(0); }

    dbName = await text({
      message: 'PostgreSQL Database Name:',
      placeholder: 'reyhan_db',
      defaultValue: 'reyhan_db'
    })
    if (isCancel(dbName)) { cancel('Setup cancelled.'); process.exit(0); }

    dbUser = await text({
      message: 'PostgreSQL Username:',
      placeholder: 'postgres',
      defaultValue: 'postgres'
    })
    if (isCancel(dbUser)) { cancel('Setup cancelled.'); process.exit(0); }

    dbPass = await password({
      message: 'PostgreSQL Password (leave blank if none):',
      mask: '*'
    })
    if (isCancel(dbPass)) { cancel('Setup cancelled.'); process.exit(0); }

    redisHost = await text({
      message: 'Redis Host:',
      placeholder: '127.0.0.1',
      defaultValue: '127.0.0.1'
    })
    if (isCancel(redisHost)) { cancel('Setup cancelled.'); process.exit(0); }

    redisPort = await text({
      message: 'Redis Port:',
      placeholder: '6379',
      defaultValue: '6379'
    })
    if (isCancel(redisPort)) { cancel('Setup cancelled.'); process.exit(0); }

    shouldInitGit = await confirm({
      message: 'Initialize a new Git repository?',
      initialValue: true
    })
    if (isCancel(shouldInitGit)) { cancel('Setup cancelled.'); process.exit(0); }

    shouldInstallDeps = await confirm({
      message: 'Install dependencies and run initial migrations now?',
      initialValue: true
    })
    if (isCancel(shouldInstallDeps)) { cancel('Setup cancelled.'); process.exit(0); }
  }

  const targetDir = resolve(process.cwd(), targetDirInput)
  const projectName = basename(targetDir)

  const s = spinner()
  s.start(pc.green('Scaffolding Reyhan full-stack monorepo...'))

  const templateDir = resolve(import.meta.dirname, '../template')
  mkdirSync(targetDir, { recursive: true })
  cpSync(templateDir, targetDir, { recursive: true })

  // Make reyhan script executable
  const reyhanScript = join(targetDir, 'reyhan')
  if (existsSync(reyhanScript)) {
    try {
      chmodSync(reyhanScript, 0o755)
    } catch {}
  }

  // Generate customized backend/.env
  const backendEnvContent = `APP_NAME="${storeName}"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

APP_LOCALE=fa
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=fa_IR

LOG_CHANNEL=stack
LOG_LEVEL=debug

# External PostgreSQL 17+ (BYOD)
DB_CONNECTION=pgsql
DB_HOST=${dbHost}
DB_PORT=${dbPort}
DB_DATABASE=${dbName}
DB_USERNAME=${dbUser}
DB_PASSWORD=${dbPass || ''}

SESSION_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
CACHE_STORE=redis

# External Redis 7+ (BYOD)
REDIS_CLIENT=phpredis
REDIS_HOST=${redisHost}
REDIS_PASSWORD=null
REDIS_PORT=${redisPort}
REDIS_DB=0
REDIS_CACHE_DB=0
REDIS_SESSION_DB=1
REDIS_QUEUE_DB=2

SANCTUM_STATEFUL_DOMAINS="localhost:3000,127.0.0.1:3000,localhost,127.0.0.1"
MAIL_MAILER=log
`
  writeFileSync(join(targetDir, 'backend/.env'), backendEnvContent, 'utf-8')

  // Generate customized frontend/.env
  const frontendEnvContent = `NODE_ENV=development
PORT=3000
HOST=0.0.0.0

NUXT_PUBLIC_SITE_URL=http://localhost:3000
NUXT_PUBLIC_API_BASE=http://localhost:8000/api/v1
`
  writeFileSync(join(targetDir, 'frontend/.env'), frontendEnvContent, 'utf-8')

  // Update frontend app.config.ts with store brand and theme color
  const appConfigPath = join(targetDir, 'frontend/app/app.config.ts')
  if (existsSync(appConfigPath)) {
    const customizedAppConfig = `export default defineAppConfig({
  ui: {
    colors: {
      primary: '${themeColor}',
      neutral: 'zinc'
    }
  },

  reyhan: {
    brand: {
      name: '${storeName}',
      slogan: 'تجربه خرید آنلاین هوشمند، سریع و مطمئن ✨',
      logoUrl: '/icon.svg',
      faviconUrl: '/icon.svg'
    },
    header: {
      sticky: true,
      showSearch: true,
      announcementBar: {
        enabled: true,
        text: 'ارسال رایگان برای خریدهای بالای ۱ میلیون تومان ✨',
        link: '/faq'
      }
    },
    footer: {
      showNewsletter: true,
      copyright: 'تمامی حقوق برای ${storeName} محفوظ است.'
    },
    translations: {
      fa: {},
      en: {}
    }
  }
})
`
    writeFileSync(appConfigPath, customizedAppConfig, 'utf-8')
  }

  s.stop(pc.green('✔ Scaffolding complete.'))

  // Run optional dependency installation
  if (shouldInstallDeps) {
    s.start(pc.cyan('Installing backend dependencies (composer install)...'))
    try {
      execSync('composer install --no-interaction --prefer-dist --optimize-autoloader', {
        cwd: join(targetDir, 'backend'),
        stdio: 'ignore'
      })
      s.stop(pc.green('✔ Backend dependencies installed.'))
    } catch {
      s.stop(pc.yellow('⚠ Composer install skipped or failed. Run composer install manually.'))
    }

    s.start(pc.cyan('Installing frontend dependencies (pnpm install)...'))
    try {
      execSync('pnpm install --silent', {
        cwd: join(targetDir, 'frontend'),
        stdio: 'ignore'
      })
      s.stop(pc.green('✔ Frontend dependencies installed.'))
    } catch {
      s.stop(pc.yellow('⚠ pnpm install skipped or failed. Run pnpm install manually.'))
    }

    s.start(pc.cyan('Provisioning database, app key, and storage symlink...'))
    try {
      execSync('./reyhan install', {
        cwd: targetDir,
        stdio: 'ignore'
      })
      s.stop(pc.green('✔ Database migrated and environment provisioned.'))
    } catch {
      s.stop(pc.yellow('⚠ Provisioning skipped. Run ./reyhan install when database is accessible.'))
    }
  }

  // Initialize Git
  if (shouldInitGit) {
    try {
      execSync('git init && git add -A && git commit -m "feat: initial commit from create-reyhan"', {
        cwd: targetDir,
        stdio: 'ignore'
      })
    } catch {}
  }

  // Outro & Quick Start Card
  outro(pc.green(pc.bold('🎉 Reyhan Commerce Initialized Successfully!')))

  console.log(`
  ${pc.bold(pc.white('🚀 Quick Start Guide:'))}

    ${pc.green(`cd ${projectName}`)}
    ${pc.green(`./reyhan doctor`)}     ${pc.gray('# Verify environment and database')}
    ${pc.green(`./reyhan dev`)}        ${pc.gray('# Start backend and frontend concurrently')}

  ${pc.bold(pc.white('🌐 Access Endpoints:'))}
    ${pc.yellow('•')} Storefront:   ${pc.bold(pc.green('http://localhost:3000'))}
    ${pc.yellow('•')} Admin Panel:  ${pc.bold(pc.green('http://localhost:8000/admin'))}
    ${pc.yellow('•')} REST API:     ${pc.bold(pc.green('http://localhost:8000/api/v1'))}
    ${pc.yellow('•')} API Docs:     ${pc.bold(pc.green('http://localhost:8000/docs/api'))}
`)
}

main().catch((err) => {
  console.error(pc.red('Error creating project:'), err)
  process.exit(1)
})
