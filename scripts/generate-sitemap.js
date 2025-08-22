#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

// Конфигурация сайта
const SITE_CONFIG = {
  baseUrl: 'https://capital-craft.ru',
  pages: [
    {
      path: '/',
      priority: '1.0',
      changefreq: 'daily'
    },
    {
      path: '/faq',
      priority: '0.9',
      changefreq: 'daily'
    }
  ]
};

// Генерируем текущую дату и время в формате YYYY-MM-DDTHH:MM:SS
function getCurrentDateTime() {
  const now = new Date();
  return now.toISOString().slice(0, 19); // YYYY-MM-DDTHH:MM:SS
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
