const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

// Функция для компиляции LESS файла
function compileLess(lessPath, cssPath) {
  try {
    // Создаем директорию для CSS если её нет
    const cssDir = path.dirname(cssPath);
    if (!fs.existsSync(cssDir)) {
      fs.mkdirSync(cssDir, { recursive: true });
    }
    
    // Компилируем LESS в CSS
    execSync(`npx lessc "${lessPath}" "${cssPath}"`, { stdio: 'inherit' });
    console.log(`✅ Скомпилирован: ${lessPath} → ${cssPath}`);
  } catch (error) {
    console.error(`❌ Ошибка компиляции ${lessPath}:`, error.message);
  }
}

// Функция для рекурсивного поиска LESS файлов
function findLessFiles(dir) {
  const files = [];
  
  function scan(currentDir) {
    const items = fs.readdirSync(currentDir);
    
    for (const item of items) {
      const fullPath = path.join(currentDir, item);
      const stat = fs.statSync(fullPath);
      
      if (stat.isDirectory()) {
        scan(fullPath);
      } else if (item.endsWith('.less')) {
        files.push(fullPath);
      }
    }
  }
  
  scan(dir);
  return files;
}

// Основная функция
function buildAllLess() {
  console.log('🚀 Начинаю компиляцию LESS файлов...\n');
  
  const lessDir = 'templates/capitalcraft/less';
  const cssDir = 'templates/capitalcraft/css';
  
  if (!fs.existsSync(lessDir)) {
    console.error(`❌ Директория ${lessDir} не найдена`);
    return;
  }
  
  const lessFiles = findLessFiles(lessDir);
  console.log(`📁 Найдено ${lessFiles.length} LESS файлов\n`);
  
  for (const lessFile of lessFiles) {
    // Определяем путь для CSS файла
    const relativePath = path.relative(lessDir, lessFile);
    const cssFile = path.join(cssDir, relativePath.replace('.less', '.css'));
    
    compileLess(lessFile, cssFile);
  }
  
  console.log('\n🎉 Компиляция завершена!');
}

// Запускаем если скрипт вызван напрямую
if (require.main === module) {
  buildAllLess();
}

module.exports = { buildAllLess, compileLess };
