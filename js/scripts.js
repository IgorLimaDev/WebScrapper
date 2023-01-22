document.addEventListener("DOMContentLoaded", function() {

	//dispara evento ao enviar formulário
	document.querySelector(".form-scrap").addEventListener("submit", function(e) {
		//previne a página de atualizar no submit
		e.preventDefault();

		
		//Pega a URL do campo e cria objeto para ser enviado via POST
		let url = document.querySelector(".campo-url").value;
		if(url == "") {alert("Por favor, preencha o campo de URL."); return false;}
		var dados = {
			url: url
		}

		document.querySelector(".loader").innerHTML = "Recuperando dados, aguarde..."

		//Enviar a url para o processamento no backend
		fetch('php/scrappage.php', {
			method: "POST",
			body: JSON.stringify(dados)
		}).then((response) => response.text())
		.then((dados) => {
			document.querySelector(".loader").innerHTML = "";
			//Retorna e renderiza os dados obtidos
			let dadosJson = JSON.parse(dados);
			
			//Preenche a tabela com os dados coletados do site
			document.querySelector(".conteudo-titulo").innerHTML = dadosJson.titulo;
			document.querySelector(".conteudo-descricao").innerHTML = dadosJson.descricao;

			//Limpa tabela de links
			document.querySelector(".lista-links table").innerHTML = "";
			document.querySelector(".label-links").innerHTML = "Lista de Links ("+dadosJson.links.length+")";
			//Preenche com os links
			for(i = 0;i < dadosJson.links.length;i++) {
				document.querySelector(".lista-links table").innerHTML+= "<tr><td>" + dadosJson.links[i] + "</td></tr>";
			}

			//Limpa tabela de imagens
			document.querySelector(".lista-imagens table").innerHTML = "";
			document.querySelector(".label-imagens").innerHTML = "Lista de Imagens ("+dadosJson.imagens.length+")";
			//Preenche com as imagens
			for(i = 0;i < dadosJson.imagens.length;i++) {
				document.querySelector(".lista-imagens table").innerHTML+= "<tr><td>" + dadosJson.imagens[i] + "</td></tr>";
			}

		}).catch((erro) => {
			alert("Ocorreu um erro ao processar o site, por favor, tente outra URL.");
		});

	});
});