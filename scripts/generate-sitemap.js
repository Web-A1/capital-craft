#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

// Конфигурация сайта
const SITE_CONFIG = {
  baseUrl: 'https://capital-craft.ru',
  pages: [
    {
      path: '/',
      priority: '0.9',
      changefreq: 'daily'
    },
    {
      path: '/faq',
      priority: '1.0',
      changefreq: 'daily'
    }
  ]
};

// Генерируем текущую дату и время в формате YYYY-MM-DDTHH:MM:SS (локальное время)
function getCurrentDateTime() {
  const now = new Date();
  
  // Получаем локальное время (московское UTC+3)
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, '0');
  const day = String(now.getDate()).padStart(2, '0');
  const hours = String(now.getHours()).padStart(2, '0');
  const minutes = String(now.getMinutes()).padStart(2, '0');
  const seconds = String(now.getSeconds()).padStart(2, '0');
  
  return `${year}-${month}-${day}T${hours}:${minutes}:${seconds}`;
}

// Генерируем XML sitemap
function generateSitemapXML() {
  const currentDateTime = getCurrentDateTime();
  
  let xml = '<?xml version="1.0" encoding="UTF-8"?>\n';
  xml += '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n';
  
  SITE_CONFIG.pages.forEach(page => {
    xml += '  <url>\n';
    xml += `    <loc>${SITE_CONFIG.baseUrl}${page.path}</loc>\n`;
    xml += `    <lastmod>${currentDateTime}</lastmod>\n`;
    xml += `    <changefreq>${page.changefreq}</changefreq>\n`;
    xml += `    <priority>${page.priority}</priority>\n`;
    xml += '  </url>\n';
  });
  
  xml += '</urlset>';
  
  return xml;
}

// Основная функция
function main() {
  try {
    const sitemapContent = generateSitemapXML();
    const sitemapPath = path.join(__dirname, '..', 'sitemap.xml');
    
    // Записываем новый sitemap
    fs.writeFileSync(sitemapPath, sitemapContent, 'utf8');
    
    console.log('✅ Sitemap.xml успешно обновлен!');
    console.log(`📅 Дата и время обновления: ${getCurrentDateTime()}`);
    console.log(`📁 Файл сохранен: ${sitemapPath}`);
    
    // Выводим содержимое для проверки
    console.log('\n📋 Содержимое sitemap:');
    console.log(sitemapContent);
    
  } catch (error) {
    console.error('❌ Ошибка при генерации sitemap:', error.message);
    process.exit(1);
  }
}

// Запускаем скрипт
if (require.main === module) {
  main();
}

module.exports = { generateSitemapXML, getCurrentDateTime };
