package com.example.pokemon.service;

import com.example.pokemon.entity.Moves;
import com.example.pokemon.form.MoveForm;
import com.example.pokemon.repository.MoveRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import java.util.Optional;

@Service
public class MoveServiceImpl implements MoveService {
    @Override
    public void deleteMove(Integer id) {
        //使用されている技かチェック
        if (moveRepository.existsReferencedInPokemon(id)) {
            throw new IllegalStateException("この技はポケモンに使用されているため削除できません");
        }

        //削除
        moveRepository.deleteById(id);
    }
}
