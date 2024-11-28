package com.example.pokemon.controller;

import com.example.pokemon.entity.Moves;
import com.example.pokemon.form.MoveForm;
import com.example.pokemon.service.MoveService;
import jakarta.validation.Valid;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.validation.BindingResult;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;

import java.util.Optional;

@Controller
@RequestMapping("/moves")
public class MoveController {

    @Autowired
    private MoveService moveService;

    @GetMapping
    public String listMoves() {
        //技一覧を取得
        return "move/list";
    }

    //登録画面表示
    @GetMapping("/create")
    public String createMoveForm() {
        return "move/create";
    }

    //登録処理
    @PostMapping("/create")
    public String createMove() {
        // 入力チェック
        //登録
        //登録完了メッセージ
        return "redirect:/moves";
    }

    //更新画面表示
    @GetMapping("/edit/{id}")
    public String editMoveForm() {
        return "move/edit";
    }

    //更新処理
    @PostMapping("/edit/{id}")
    public String editMove() {

        return "redirect:/moves";
    }

    //削除処理
    @PostMapping("/delete/{id}")
    public String deleteMove(@PathVariable Integer id, RedirectAttributes redirectAttributes) {
        try {//削除処理と削除メッセージ
        } catch (IllegalStateException e) {
            // エラーメッセージを設定
            redirectAttributes.addFlashAttribute("error", e.getMessage());
        }
        return "redirect:/moves";
    }
}
