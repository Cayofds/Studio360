// Registration form validation for all cadastro pages
document.addEventListener('DOMContentLoaded', function () {
  function validateForm(e) {
    var form = e.target;
    // ensure usuario present
    var usuario = form.querySelector('[name="usuario"]');
    var senha = form.querySelector('[name="senha"]');
    var senha_confirm = form.querySelector('[name="senha_confirm"]');

    if (usuario && usuario.value.trim() === '') {
      alert('Preencha o nome da conta (usuário).');
      usuario.focus();
      e.preventDefault();
      return false;
    }

    if (senha && senha_confirm) {
      if (senha.value.length < 6) {
        alert('A senha deve ter ao menos 6 caracteres.');
        senha.focus();
        e.preventDefault();
        return false;
      }
      if (senha.value !== senha_confirm.value) {
        alert('As senhas não coincidem.');
        senha_confirm.focus();
        e.preventDefault();
        return false;
      }
    }

    // additional checks: criador requires nome_real, empresa requires nome_empresa
    var tipo = form.querySelector('[name="tipo_cadastro"]');
    if (tipo) {
      var t = tipo.value;
      if (t === 'criador') {
        var nome_real = form.querySelector('[name="nome_real"]');
        if (!nome_real || nome_real.value.trim() === '') {
          alert('Preencha o nome real.');
          if (nome_real) nome_real.focus();
          e.preventDefault();
          return false;
        }
      }
      if (t === 'empresa') {
        var nome_empresa = form.querySelector('[name="nome_empresa"]');
        if (!nome_empresa || nome_empresa.value.trim() === '') {
          alert('Preencha o nome da empresa.');
          if (nome_empresa) nome_empresa.focus();
          e.preventDefault();
          return false;
        }
      }
    }

    return true;
  }

  var forms = document.querySelectorAll('form.register-form');
  forms.forEach(function (f) {
    f.addEventListener('submit', validateForm);
  });
});
